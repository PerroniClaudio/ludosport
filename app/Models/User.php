<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Scout\Searchable;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class User extends Authenticatable implements MustVerifyEmail {
    use HasFactory, Notifiable, HasApiTokens, Searchable, Billable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'surname',
        'email',
        'password',
        'subscription_year',
        'academy_id',
        'school_id',
        'nation_id',
        'unique_code',
        'profile_picture',
        'birthday',
        'active_fee_id',
        'has_paid_fee',
        'battle_name',
        'instagram',
        'telegram',
        'bio',
        'how_found_us',
        'rank_id',
        'gender'
    ];

    public function toSearchableArray() {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'surname' => $this->surname,
            'email' => $this->email,
            'battle_name' => $this->battle_name,
            'is_disabled' => $this->is_disabled,
        ];
    }


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function boot() {
        parent::boot();

        static::creating(function ($user) {

            if ($user->email) {
                $user->email = strtolower($user->email);
            }

            // Se l'utente non ha inserito il battle name o se è già esistente viene generato in automatico
            if (!$user->battle_name || User::where('battle_name', $user->battle_name)->exists()) {
                $user->battle_name = $user->generateBattleName();
            }

            if (is_null($user->rank_id)) {
                $user->rank_id = Rank::first()->id; // Imposta il valore predefinito per rank_id
            }

            if (
                is_null($user->unique_code) ||
                $user->unique_code == "" ||
                User::where('unique_code', $user->unique_code)->count() > 0
            ) {
                $code_valid = false;
                while (!$code_valid) {
                    $unique_code = Str::random(4) . "-" . Str::random(4) . "-" . Str::random(4) . "-" . Str::random(4);
                    $code_valid = User::where('unique_code', $unique_code)->count() == 0;
                }
                $user->unique_code = $unique_code;
            }
        });
    }


    public function nation() {
        return $this->belongsTo(Nation::class);
    }

    // Restutuisce le accademie in cui fa parte del personale
    public function academies() {
        return $this->belongsToMany(Academy::class, 'academies_personnel', 'user_id', 'academy_id')->withPivot('is_primary');
    }

    // Restituisce l'accademia principale (per il personale)
    public function primaryAcademy() {
        return $this->academies()->where('is_disabled', false)->wherePivot('is_primary', true)->first();
    }

    // Imposta accademia primaria (per il personale)
    public function setPrimaryAcademy($academyId) {
        // Rimuove l'attuale accademia principale del personale e imposta la nuova
        if ($this->primaryAcademy()) {
            $this->academies()->updateExistingPivot($this->primaryAcademy()->id, ['is_primary' => false]);
        }
        $this->academies()->updateExistingPivot($academyId, ['is_primary' => true]);
    }

    // Restituisce le accademie in cui fa parte degli atleti
    public function academyAthletes() {
        return $this->belongsToMany(Academy::class, 'academies_athletes', 'user_id', 'academy_id')->withPivot('is_primary');
    }

    // Restituisce l'accademia principale (per gli atleti)
    public function primaryAcademyAthlete() {

        $academy = $this->academyAthletes()->where('is_disabled', false)->wherePivot('is_primary', true)->first();

        if (!$academy) {
            $academy = $this->academyAthletes()->where('is_disabled', false)->first();
        }

        return $academy;
    }

    // Imposta accademia primaria (per gli atleti)
    public function setPrimaryAcademyAthlete($academyId) {
        // Rimuove l'attuale accademia principale dell'atleta e imposta la nuova
        if ($this->primaryAcademyAthlete()) {
            $this->academyAthletes()->updateExistingPivot($this->primaryAcademyAthlete()->id, ['is_primary' => false]);
        }
        $this->academyAthletes()->updateExistingPivot($academyId, ['is_primary' => true]);
    }

    // Restituisce le scuole in cui fa parte del personale
    public function schools() {
        return $this->belongsToMany(School::class, 'schools_personnel', 'user_id', 'school_id')->withPivot('is_primary');
    }

    // Restituisce la scuola principale (per il personale)
    public function primarySchool() {
        return $this->schools()->where('is_disabled', false)->wherePivot('is_primary', true)->first();
    }

    // Imposta scuola primaria (per il personale)
    public function setPrimarySchool($schoolId) {
        // Rimuove l'attuale scuola principale del personale e imposta la nuova
        if ($this->primarySchool()) {
            $this->schools()->updateExistingPivot($this->primarySchool()->id, ['is_primary' => false]);
        }
        $this->schools()->updateExistingPivot($schoolId, ['is_primary' => true]);
    }

    // Restituisce le scuole in cui fa parte degli atleti
    public function schoolAthletes() {
        return $this->belongsToMany(School::class, 'schools_athletes', 'user_id', 'school_id')->withPivot('is_primary');
    }

    // Restituisce la scuola principale (per gli atleti)
    public function primarySchoolAthlete() {
        return $this->schoolAthletes()->where('is_disabled', false)->wherePivot('is_primary', true)->first();
    }

    // Imposta scuola primaria (per gli atleti)
    public function setPrimarySchoolAthlete($schoolId) {
        // Rimuove l'attuale scuola principale dell'atleta e imposta la nuova
        if ($this->primarySchoolAthlete()) {
            $this->schoolAthletes()->updateExistingPivot($this->primarySchoolAthlete()->id, ['is_primary' => false]);
        }
        $this->schoolAthletes()->updateExistingPivot($schoolId, ['is_primary' => true]);
    }

    public function clans() {
        return $this->belongsToMany(Clan::class, 'clans_users', 'user_id', 'clan_id');
    }

    public function clansPersonnel() {
        return $this->belongsToMany(Clan::class, 'clans_personnel', 'user_id', 'clan_id');
    }

    public function roles() {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id');
    }

    public function seenAnnouncements() {
        return $this->belongsToMany(Announcement::class, 'announcement_users', 'user_id', 'announcement_id');
    }

    public function fees() {
        return $this->hasMany(Fee::class);
    }

    public function imports() {
        return $this->hasMany(Import::class);
    }

    public function exports() {
        return $this->hasMany(Export::class);
    }

    // Eventi creati dall'utente
    public function events() {
        return $this->hasMany(Event::class);
    }

    // Eventi in cui l'utente è stato inserito come personale
    public function eventsPersonnel() {
        return $this->belongsToMany(Event::class, 'events_personnel', 'user_id', 'event_id');
    }

    // Eventi a cui l'utente partecipa da atleta
    public function eventResults() {
        return $this->hasMany(EventResult::class);
    }

    public function customRoles() {
        return $this->belongsToMany(CustomRole::class, 'custom_roles_users', 'user_id', 'custom_role_id');
    }

    public function weaponForms() {
        return $this->belongsToMany(WeaponForm::class, 'weapon_forms_users', 'user_id', 'weapon_form_id')
            // ->withPivot('created_at as awarded_at');
            ->withPivot('awarded_at as awarded_at');
    }

    // Solo le richieste approvate. Le altre le vedono gli admin partendo da weaponForms
    // Weapon forms personnel è per gli istruttori
    public function weaponFormsPersonnel() {
        return $this->belongsToMany(WeaponForm::class, 'weapon_forms_personnel', 'user_id', 'weapon_form_id')
            // ->withPivot('created_at as awarded_at');
            ->withPivot('awarded_at as awarded_at');
    }

    // Weapon forms technicians è per i tecnici
    public function weaponFormsTechnician() {
        return $this->belongsToMany(WeaponForm::class, 'weapon_forms_technicians', 'user_id', 'weapon_form_id')
            // ->withPivot('created_at as awarded_at');
            ->withPivot('awarded_at as awarded_at');
    }

    public function languages() {
        return $this->belongsToMany(Language::class, 'users_languages', 'user_id', 'language_id');
    }

    public function rank() {
        return $this->belongsTo(Rank::class);
    }

    public function invoices() {
        return $this->hasMany(\App\Models\Invoice::class);
    }

    public function routes() {

        $role = $this->getRole();


        switch ($role) {
            case 'admin':
                return collect([
                    (object)[
                        'label' => 'any',
                        'name' => 'admin.any.index',
                    ]
                ]);
            case 'athlete':
                return collect([
                    (object)[
                        'label' => 'announcements',
                        'active' => 'announcements.*',
                        'name' => 'athlete.announcements.index',
                    ],
                ]);
            case 'rector':
                return collect([
                    (object)[
                        'label' => 'announcements',
                        'active' => 'announcements.*',
                        'name' => 'rector.announcements.index',
                    ],
                    (object)[
                        'label' => 'accademia',
                        'active' => 'academies.*',
                        'name' => 'rector.academies.index',
                    ],
                    (object)[
                        'label' => 'scuole',
                        'active' => 'schools.*',
                        'name' => 'rector.schools.index',
                    ],
                    (object)[
                        'label' => 'clan',
                        'active' => 'clans.*',
                        'name' => 'rector.clans.index',
                    ],
                    (object)[
                        'label' => 'users',
                        'active' => 'users.*',
                        'name' => 'rector.users.index',
                    ],
                    (object)[
                        'label' => 'eventi',
                        'active' => 'events.*',
                        'name' => 'rector.events.index',
                    ],
                    (object)[
                        'label' => 'fees',
                        'active' => 'fees.*',
                        'name' => 'rector.fees.index',
                    ],
                    (object)[
                        'label' => 'imports',
                        'active' => 'imports.*',
                        'name' => 'rector.imports.index',
                    ],
                    (object)[
                        'label' => 'exports',
                        'active' => 'exports.*',
                        'name' => 'rector.exports.index',
                    ],

                ]);
            case 'dean':
                return collect([
                    (object)[
                        'label' => 'announcements',
                        'active' => 'announcements.*',
                        'name' => 'dean.announcements.index',
                    ],
                    (object)[
                        'label' => 'scuola',
                        'active' => 'schools.*',
                        'name' => 'dean.school.index',
                    ],
                    (object)[
                        'label' => 'clan',
                        'active' => 'clans.*',
                        'name' => 'dean.clans.index',
                    ],
                    (object)[
                        'label' => 'users',
                        'active' => 'users.*',
                        'name' => 'dean.users.index',
                    ],
                    (object)[
                        'label' => 'eventi',
                        'active' => 'events.*',
                        'name' => 'dean.events.index',
                    ],
                    (object)[
                        'label' => 'imports',
                        'active' => 'imports.*',
                        'name' => 'dean.imports.index',
                    ],
                    (object)[
                        'label' => 'exports',
                        'active' => 'exports.*',
                        'name' => 'dean.exports.index',
                    ],
                ]);
            case 'manager':
                return collect([
                    (object)[
                        'label' => 'announcements',
                        'active' => 'announcements.*',
                        'name' => 'manager.announcements.index',
                    ],
                    (object)[
                        'label' => 'accademia',
                        'active' => 'academy.*',
                        'name' => 'manager.academy.index',
                    ],
                    (object)[
                        'label' => 'scuole',
                        'active' => 'schools.*',
                        'name' => 'manager.schools.index',
                    ],
                    (object)[
                        'label' => 'clan',
                        'active' => 'clans.*',
                        'name' => 'manager.clans.index',
                    ],
                    (object)[
                        'label' => 'users',
                        'active' => 'users.*',
                        'name' => 'manager.users.index',
                    ],
                    (object)[
                        'label' => 'eventi',
                        'active' => 'events.*',
                        'name' => 'manager.events.index',
                    ],
                    (object)[
                        'label' => 'fees',
                        'active' => 'fees.*',
                        'name' => 'manager.fees.index',
                    ],
                    (object)[
                        'label' => 'imports',
                        'active' => 'imports.*',
                        'name' => 'manager.imports.index',
                    ],
                    (object)[
                        'label' => 'exports',
                        'active' => 'exports.*',
                        'name' => 'manager.exports.index',
                    ],
                ]);
            case 'technician':
                return collect([
                    (object)[
                        'label' => 'announcements',
                        'active' => 'announcements.*',
                        'name' => 'technician.announcements.index',
                    ],
                    (object)[
                        'label' => 'users',
                        'active' => 'users.*',
                        'name' => 'technician.users.index',
                    ],
                    (object)[
                        'label' => 'eventi',
                        'active' => 'events.*',
                        'name' => 'technician.events.index',
                    ],
                    (object)[
                        'label' => 'imports',
                        'active' => 'imports.*',
                        'name' => 'technician.imports.index',
                    ],
                    (object)[
                        'label' => 'exports',
                        'active' => 'exports.*',
                        'name' => 'technician.exports.index',
                    ],
                    // (object)[
                    //     'label' => 'istruttori',
                    //     'active' => 'istruttori.*',
                    //     'name' => 'technician.',
                    // ],
                ]);
            case 'instructor':
                return collect([
                    (object)[
                        'label' => 'users',
                        'active' => 'users.*',
                        'name' => 'instructor.users.index',
                    ],
                    (object)[
                        'label' => 'clan',
                        'active' => 'clans.*',
                        'name' => 'instructor.clans.index',
                    ],
                    (object)[
                        'label' => 'announcements',
                        'active' => 'announcements.*',
                        'name' => 'instructor.announcements.index',
                    ],
                ]);
            default:
                return collect([]);
        }
    }

    public function allowedRoles(): array {
        return $this->roles()->get()->map(function ($role) {
            return $role->name;
        })->toArray();
    }

    public function allowedRoleIds(): array {
        return $this->roles()->get()->map(function ($role) {
            return $role->id;
        })->toArray();
    }

    public function hasRole(string $role): bool {
        $selectedRole = Role::where('name', $role)->first();
        if (!$selectedRole) {
            return false;
        }
        $user = $selectedRole->users()->where('user_id', $this->id)->get();
        return $user->count() > 0;
    }

    public function getRole() {
        return session('role', $this->roles()->first()->name);
    }
    public function getActiveRoleId() {
        $active_role = Role::where('name', $this->getRole())->first();
        return $active_role->id;
    }

    public function hasAnyRole($roles) {
        if (is_string($roles)) {
            return $this->roles->contains('name', $roles);
        }

        foreach ($roles as $role) {
            if ($this->roles->contains('name', $role)) {
                return true;
            }
        }

        return false;
    }

    public function canModifyRole($roleLabel) {
        $requestingUserRole = $this->getRole();

        switch ($requestingUserRole) {
            case 'admin':
                return true;
                break;
            case 'rector':
                return !in_array($roleLabel, ['admin', 'rector', 'instructor', 'technician']);
                break;
            case 'dean':
                return !in_array($roleLabel, ['admin', 'rector', 'instructor', 'technician', 'dean', 'manager']);
                break;
            case 'manager':
                return !in_array($roleLabel, ['admin', 'rector', 'instructor', 'technician', 'dean', 'manager']);
                break;
            default:
                return false;
                break;
        }
    }

    public function getEditableRoles() {
        $authRole = $this->getRole();
        switch ($authRole) {
            case 'admin':
                return Role::all();
                break;
            case 'rector':
                return Role::all()->whereNotIn('name', ['admin', 'rector', 'instructor', 'technician']);
                break;
            case 'dean':
                return Role::all()->whereNotIn('name', ['admin', 'rector', 'instructor', 'technician', 'dean', 'manager']);
                break;
            case 'manager':
                return Role::all()->whereNotIn('name', ['admin', 'rector', 'instructor', 'technician', 'dean', 'manager']);
                break;
            default:
                return collect([]);
        }
    }

    public function isFeeExpiring() {
        $fee = $this->fees()->orderBy('created_at', 'desc')->first();
        if (!$fee) {
            return false;
        }
        $now = now();
        $expirationDate = Carbon::parse($fee->end_date);

        return $now->diffInDays($expirationDate) < 30;
    }

    public function validatePrimaryInstitutionPersonnel() {
        $user = User::find(auth()->user()->id);
        $role = $user->getRole();
        $primary = null;
        switch ($role) {
            case 'admin':
                return true;
                break;
            case 'rector':
                $primary = $user->primaryAcademy();
                if (!$primary || $primary->id == 1) {
                    return false;
                }
                return true;
                break;
            case 'dean':
                $primary = $user->primarySchool();
                if (!$primary) {
                    return false;
                }
                return true;
                break;
            case 'manager':
                $primary = $user->primaryAcademy();
                if (!$primary) {
                    return false;
                }
                return true;
                break;
            case 'instructor':
                $primary = $user->clansPersonnel()->count() > 0;
                if (!$primary) {
                    return false;
                }
                return true;
                break;
            case 'technician':
                $primary = $user->primaryAcademy();
                if (!$primary || $primary->id == 1) {
                    return false;
                }
                return true;
                break;
            default:
                return false;
                break;
        }
    }

    // Rimuove tutte le associazioni dell'atleta con tutte le accademie (escluso quella indicata) e le rispettive scuole e corsi
    public function removeAcademiesAthleteAssociations($academyExeption = null, $importingUserId = null) {
        $authUser = User::find($importingUserId ? $importingUserId : auth()->user()->id);
        // Chi usa la funzione ha già il controllo sull'autorizzazione
        // $authRole = $authUser->getRole();

        $removedCourses = $this->clans()->whereNotIn('school_id', $academyExeption ? $academyExeption->schools->pluck('id') : [])->get();
        foreach ($removedCourses as $course) {
            $this->clans()->detach($course->id);
        }
        $removedCoursesIds = $removedCourses->pluck('id')->toArray();

        $removedSchools = $this->schoolAthletes()->where('academy_id', '!=', $academyExeption->id ?? null)->get();
        foreach ($removedSchools as $school) {
            $this->schoolAthletes()->detach($school->id);
        }
        $removedSchoolsIds = $removedSchools->pluck('id')->toArray();

        $removedAcademies = $this->academyAthletes()->where('academy_id', '!=', $academyExeption->id ?? null)->get();
        foreach ($removedAcademies as $acad) {
            $this->academyAthletes()->detach($acad->id);
        }
        $removedAcademiesIds = $removedAcademies->pluck('id')->toArray();
        // Metto tutti i dati su tutti e tre i canali. Si può modificare in futuro
        Log::channel('user')->info('Removed athlete associations', [
            'made_by' => $authUser->id,
            'athlete' => $this->id,
            'academies' => $removedAcademiesIds,
            'schools' => $removedSchoolsIds,
            'courses' => $removedCoursesIds,
        ]);
    }

    // Rimuove l'associazione del personnel con l'accademia indicata e le rispettive scuole e corsi associati
    public function removeAcademyPersonnelAssociations($academyToRemove = null) {
        if ($academyToRemove == null) {
            return;
        }
        $authUser = User::find(auth()->user()->id);
        // Chi usa la funzione ha già il controllo sull'autorizzazione
        // $authRole = $authUser->getRole();

        $removedCourses = $this->clansPersonnel()->whereIn('school_id', $academyToRemove ? $academyToRemove->schools->pluck('id') : [])->get();
        foreach ($removedCourses as $course) {
            $this->clansPersonnel()->detach($course->id);
        }
        $removedCoursesIds = $removedCourses->pluck('id')->toArray();

        $removedSchools = $this->schools()->where('academy_id', $academyToRemove->id)->get();
        foreach ($removedSchools as $school) {
            $this->schools()->detach($school->id);
        }
        $removedSchoolsIds = $removedSchools->pluck('id')->toArray();

        $removedAcademies = $this->academies()->where('academy_id', $academyToRemove->id)->get();
        foreach ($removedAcademies as $acad) {
            $this->academies()->detach($acad->id);
        }
        $removedAcademiesIds = $removedAcademies->pluck('id')->toArray();
        // Metto tutti i dati su tutti e tre i canali. Si può modificare in futuro
        Log::channel('user')->info('Removed personnel associations', [
            'made_by' => $authUser->id,
            'personnel' => $this->id,
            'academies' => $removedAcademiesIds,
            'schools' => $removedSchoolsIds,
            'courses' => $removedCoursesIds,
        ]);
    }

    // Rimuove tutte le associazioni del personnel con la scuola indicata e i rispettivi corsi
    public function removeSchoolPersonnelAssociations($schoolToRemove = null) {
        if ($schoolToRemove == null) {
            return;
        }
        $authUser = User::find(auth()->user()->id);
        // Chi usa la funzione ha già il controllo sull'autorizzazione
        // $authRole = $authUser->getRole();

        $removedCourses = $this->clansPersonnel()->where('school_id', $schoolToRemove->id)->get();
        foreach ($removedCourses as $course) {
            $this->clansPersonnel()->detach($course->id);
        }
        $removedCoursesIds = $removedCourses->pluck('id')->toArray();

        $this->schools()->detach($schoolToRemove->id);
        $removedSchoolsIds = [$schoolToRemove->id];

        // Metto tutti i dati su tutti e tre i canali. Si può modificare in futuro
        Log::channel('user')->info('Removed personnel associations', [
            'made_by' => $authUser->id,
            'personnel' => $this->id,
            'schools' => $removedSchoolsIds,
            'courses' => $removedCoursesIds,
        ]);
    }

    // Rimuove l'associazione dell'atleta con la scuola indicata e i rispettivi corsi
    public function removeSchoolAthleteAssociations($schoolToRemove = null) {
        $authUser = User::find(auth()->user()->id);
        // Chi usa la funzione ha già il controllo sull'autorizzazione
        // $authRole = $authUser->getRole();
        $removedCourses = $this->clans()->where('school_id', $schoolToRemove->id)->get();
        foreach ($removedCourses as $course) {
            $this->clans()->detach($course->id);
        }
        $removedCoursesIds = $removedCourses->pluck('id')->toArray();

        $this->schoolAthletes()->detach($schoolToRemove->id);
        $removedSchoolsIds = [$schoolToRemove->id];

        // Metto tutti i dati su tutti e tre i canali. Si può modificare in futuro
        Log::channel('user')->info('Removed athlete associations', [
            'made_by' => $authUser->id,
            'athlete' => $this->id,
            'schools' => $removedSchoolsIds,
            'courses' => $removedCoursesIds,
        ]);
    }

    // Rimuove tutte le associazioni del personnel con la scuola indicata e i rispettivi corsi
    public function removeClanPersonnelAssociations($clanToRemove = null) {
        if ($clanToRemove == null) {
            return;
        }
        $authUser = User::find(auth()->user()->id);
        // Chi usa la funzione ha già il controllo sull'autorizzazione
        // $authRole = $authUser->getRole();

        $removedCourse = $this->clansPersonnel()->where('clan_id', $clanToRemove->id)->first();
        if ($removedCourse) {
            $this->clansPersonnel()->detach($removedCourse->id);
            $removedCourseId = $removedCourse->id;

            // Metto tutti i dati su tutti e tre i canali. Si può modificare in futuro
            Log::channel('user')->info('Removed personnel associations', [
                'made_by' => $authUser->id,
                'personnel' => $this->id,
                'courses' => [$removedCourseId],
            ]);
        }
    }

    // Rimuove l'associazione dell'atleta con la scuola indicata e i rispettivi corsi
    public function removeClanAthleteAssociations($clanToRemove = null) {
        if ($clanToRemove == null) {
            return;
        }
        $authUser = User::find(auth()->user()->id);
        // Chi usa la funzione ha già il controllo sull'autorizzazione
        // $authRole = $authUser->getRole();

        $removedCourse = $this->clans()->where('clan_id', $clanToRemove->id)->first();
        if ($removedCourse) {
            $this->clans()->detach($removedCourse->id);
            $removedCourseId = $removedCourse->id;

            // Metto tutti i dati su tutti e tre i canali. Si può modificare in futuro
            Log::channel('user')->info('Removed athlete associations', [
                'made_by' => $authUser->id,
                'athlete' => $this->id,
                'courses' => [$removedCourseId],
            ]);
        }
    }

    // Usatp negli import per capire l'autorizzazione per associazioni di atleti ad accademie, scuole e corsi. la parte di istruttore e tecnico non mi interessa per ora
    public function getHighestRole() {
        $highestRole = null;
        if ($this->hasRole('admin')) {
            $highestRole = 'admin';
        }
        if (!$highestRole && $this->hasRole('rector')) {
            $highestRole = 'rector';
        }
        if (!$highestRole && $this->hasRole('dean')) {
            $highestRole = 'dean';
        }
        if (!$highestRole && $this->hasRole('manager')) {
            $highestRole = 'manager';
        }
        if (!$highestRole && $this->hasRole('instructor')) {
            $highestRole = 'instructor';
        }
        if (!$highestRole && $this->hasRole('technician')) {
            $highestRole = 'technician';
        }
        return $highestRole;
    }

    public function generateBattleName() {
        $number = 10;
        $tempBattleName = $this->name . $this->surname . $number;
        while (User::where('battle_name', $tempBattleName)->count() > 0) {
            $number++;
            $tempBattleName = $this->name . $this->surname . $number;
        }
        return $tempBattleName;
    }
}
