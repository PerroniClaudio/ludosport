<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Scout\Searchable;
use App\Models\Invoice as Invoice;

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
    ];

    public function toSearchableArray() {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'surname' => $this->surname,
            'email' => $this->email,
            'battle_name' => $this->battle_name,
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

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            if (is_null($user->rank_id)) {
                $user->rank_id = Rank::first()->id; // Imposta il valore predefinito per rank_id
            }
        });
    }


    public function nation() {
        return $this->belongsTo(Nation::class);
    }

    public function academies() {
        return $this->belongsToMany(Academy::class, 'academies_personnel', 'user_id', 'academy_id');
    }

    public function academyAthletes() {
        return $this->belongsToMany(Academy::class, 'academies_athletes', 'user_id', 'academy_id');
    }

    public function schools() {
        return $this->belongsToMany(School::class, 'schools_personnel', 'user_id', 'school_id');
    }

    public function schoolAthletes() {
        return $this->belongsToMany(School::class, 'schools_athletes', 'user_id', 'school_id');
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

    public function imports() {
        return $this->hasMany(Import::class);
    }

    public function exports() {
        return $this->hasMany(Export::class);
    }

    public function events() {
        return $this->hasMany(Event::class);
    }

    public function eventResults() {
        return $this->hasMany(EventResult::class);
    }

    public function customRoles() {
        return $this->belongsToMany(CustomRole::class, 'custom_roles_users', 'user_id', 'custom_role_id');
    }

    public function weaponForms() {
        return $this->belongsToMany(WeaponForm::class, 'weapon_forms_users', 'user_id', 'weapon_form_id');
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
                ]);
            case 'manager':
                return collect([
                    (object)[
                        'label' => 'announcements',
                        'active' => 'announcements.*',
                        'name' => 'manager.announcements.index',
                    ],
                    (object)[
                        'label' => 'scuola',
                        'active' => 'schools.*',
                        'name' => 'manager.school.index',
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
                        'name' => 'dashboard',
                    ],
                    (object)[
                        'label' => 'eventi',
                        'active' => 'events.*',
                        'name' => 'technician.events.index',
                    ],
                    (object)[
                        'label' => 'istruttori',
                        'active' => 'istruttori.*',
                        'name' => 'dashboard',
                    ],
                ]);
            case 'instructor':
                return collect([
                    (object)[
                        'label' => 'announcements',
                        'active' => 'announcements.*',
                        'name' => 'instructor.announcements.index',
                    ],
                    (object)[
                        'label' => 'users',
                        'active' => 'users.*',
                        'name' => 'dashboard',
                    ],
                    (object)[
                        'label' => 'eventi',
                        'active' => 'events.*',
                        'name' => 'dashboard',
                    ],
                    (object)[
                        'label' => 'clan',
                        'active' => 'clans.*',
                        'name' => 'dashboard',
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
}
