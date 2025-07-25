<?php

namespace App\Http\Controllers;

use App\Models\Academy;
use App\Models\Clan;
use App\Models\Role;
use App\Models\School;
use App\Models\User;
use App\Models\WeaponForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ClanController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index() {
        //
        $authUser = User::find(auth()->user()->id);
        $authRole = $authUser->getRole();

        /*
        ! Controllo da reworkare

        if (!$authUser->validatePrimaryInstitutionPersonnel()) {
            return redirect()->route("dashboard")->with('error', 'You are not authorized to access this page!');
        }
            
        */

        switch ($authRole) {
            case 'admin':
                $clans = Clan::orderBy('created_at', 'desc')->where('is_disabled', '0')->with(['school'])->get();
                break;
            case 'rector':
            case 'manager':
                $primaryAcademy = $authUser->primaryAcademy();
                $clans = Clan::where('is_disabled', '0')->whereIn('school_id', $primaryAcademy->schools->pluck('id'))->with(['school'])->get();
                break;
            case 'dean':
                $authUserSchool = $authUser->primarySchool() ?? null;
                if (!$authUserSchool) {
                    return redirect()->route('dashboard')->with('error', 'You don\'t have a school assigned!');
                }
                $clans = $authUser->primarySchool() ? $authUser->primarySchool()->clan : [];
                break;
            case 'instructor':
                // Gli istruttori possono vedere tutti i corsi delle accademie a cui sono associati
                // $clans = $authUser->clansPersonnel;

                $academies = $authUser->academies;
                $clans = [];

                foreach ($academies as $academy) {
                    $schools = $academy->schools;
                    foreach ($schools as $school) {
                        foreach ($school->clan as $clan) {
                            $clans[] = $clan;
                        }
                    }
                }

                break;
            default:
                $clans = [];
                break;
        }

        foreach ($clans as $key => $clan) {
            $clans[$key]->school_name = $clan->school->name;
            $clans[$key]->academy_name = $clan->school->academy->name;
            // Si potrebbe aggiungere un valore che indichi se l'istruttore appartiene al clan o solo alla sua scuola
        }

        $viewPath = $authRole === 'admin' ? 'clan.index' : 'clan.' . $authRole . '.index';
        return view($viewPath, [
            'clans' => $clans
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {
        $authUser = User::find(auth()->user()->id);
        $authRole = $authUser->getRole();
        if (!in_array($authRole, ['admin', 'rector', 'dean', 'manager'])) {
            return redirect()->route('dashboard')->with('error', 'You are not authorized to access this page.');
        }

        switch ($authRole) {
            case 'admin':
                $schools = School::all();
                break;
            case 'rector':
            case 'manager':
                $schools = $authUser->primaryAcademy() ? $authUser->primaryAcademy()->schools : [];
                break;
            case 'dean':
                $schools = collect($authUser->primarySchool() ? [$authUser->primarySchool()] : []);
                break;
            default:
                $schools = [];
                break;
        }

        $formatted_schools = [
            [
                'value' => '',
                'label' => 'Select a school'
            ]
        ];

        foreach ($schools as $key => $school) {
            $formatted_schools[] = [
                'value' => $school->id,
                'label' => $school->name
            ];
        }

        $weaponForms = WeaponForm::all();
        $weaponForms->prepend((object)[
            'id' => 0,
            'name' => 'Other'
        ]);

        $viewPath = $authRole === 'admin' ? 'clan.create' : 'clan.' . $authRole . '.create';
        return view($viewPath, [
            'schools' => $formatted_schools,
            'weaponForms' => $weaponForms
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {
        //
        $request->validate([
            'name' => 'required',
            'school_id' => 'required',
        ]);

        $authUser = User::find(auth()->user()->id);
        $authRole = $authUser->getRole();
        if (!in_array($authRole, ['admin', 'rector', 'dean', 'manager'])) {
            return redirect()->route('dashboard')->with('error', 'You are not authorized to access this page.');
        }

        switch ($authRole) {
            case 'admin':
                break;
            case 'rector':
            case 'manager':
                if ($authUser->primaryAcademy()) {
                    $school = $authUser->primaryAcademy()->schools->where('id', $request->school_id)->first();
                } else {
                    $school = null;
                }
                if (!$school) {
                    return redirect()->route('dashboard')->with('error', 'You are not authorized to access this page.');
                }
                break;
            case 'dean':
                $school = $authUser->primarySchool() ?? null;
                if (!$school || ($school->id != $request->school_id)) {
                    return redirect()->route('dashboard')->with('error', 'You are not authorized to access this page.');
                }
                break;
            default:
                return redirect()->route('dashboard')->with('error', 'You are not authorized to access this page.');
                break;
        }


        $slug = Str::slug($request->name);

        if (Clan::where('slug', $slug)->exists()) {
            $slug = $slug . '-' . time();
        }

        if ($request->weapon_form_id == 0) {
            $request->weapon_form_id = null;
        }

        $clan = Clan::create([
            'name' => $request->name,
            'school_id' => $request->school_id,
            'slug' => $slug,
            'weapon_form_id' => $request->weapon_form_id
        ]);

        $redirectPath = $authRole === 'admin' ? 'clans.edit' : $authRole . '.clans.edit';
        return redirect()->route($redirectPath, $clan)->with('success', 'Course created successfully.');
    }

    public function storeForSchool(Request $request) {
        //
        $request->validate([
            'name' => 'required',
        ]);

        $authUser = User::find(auth()->user()->id);
        $authRole = $authUser->getRole();
        if (!in_array($authRole, ['admin', 'rector', 'dean', 'manager'])) {
            return redirect()->route('dashboard')->with('error', 'You are not authorized to access this page.');
        }

        switch ($authRole) {
            case 'admin':
                break;
            case 'rector':
            case 'manager':
                if ($authUser->primaryAcademy()) {
                    $school = $authUser->primaryAcademy()->schools->where('id', $request->school_id)->first();
                } else {
                    $school = null;
                }
                if (!$school) {
                    return redirect()->route('dashboard')->with('error', 'You are not authorized to access this page.');
                }
                break;
            case 'dean':
                $school = $authUser->primarySchool() ?? null;
                if (!$school || ($school->id != $request->school_id)) {
                    return redirect()->route('dashboard')->with('error', 'You are not authorized to access this page.');
                }
                break;
            default:
                return redirect()->route('dashboard')->with('error', 'You are not authorized to access this page.');
                break;
        }

        $slug = Str::slug($request->name);

        if (Clan::where('slug', $slug)->exists()) {
            $slug = $slug . '-' . time();
        }

        $clan = Clan::create([
            'name' => $request->name,
            'school_id' => $request->school_id,
            'slug' => $slug

        ]);

        if ($request->go_to_edit_clan) {
            $redirectRoute = $authRole === 'admin' ? 'clans.edit' : $authRole . '.clans.edit';
            return redirect()->route($redirectRoute, $clan->id)->with('success', 'Course created successfully.');
        } else {
            $redirectRoute = $authRole === 'admin' ? 'schools.edit' : $authRole . '.schools.edit';
            return redirect()->route($redirectRoute, $request->school_id)->with('success', 'Course created successfully.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Clan $clan) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Clan $clan) {

        $authUser = User::find(auth()->user()->id);
        $authRole = $authUser->getRole();
        if (!in_array($authRole, ['admin', 'rector', 'dean', 'manager', 'instructor'])) {
            return redirect()->route('dashboard')->with('error', 'You are not authorized to access this page.');
        }
        if (!$clan) {
            return redirect()->route('dashboard')->with('error', 'Course not found.');
        }
        if ($clan->is_disabled && $authRole !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Course disabled.');
        }
        switch ($authRole) {
            case 'admin':
                $schools = School::all();
                break;
            case 'rector':
            case 'manager':
                $school = $authUser->primaryAcademy() ? $authUser->primaryAcademy()->schools->where('id', $clan->school_id)->first() : null;
                if (!$school) {
                    return redirect()->route('dashboard')->with('error', 'You are not authorized to access this page.');
                }
                $schools = $authUser->primaryAcademy()->schools;
                break;
            case 'dean':
                if ($clan->school_id !== ($authUser->getActiveInstitutionId() ?? null)) {
                    return redirect()->route('dashboard')->with('error', 'You are not authorized to access this page.');
                }
                $schools = $authUser->schools->where('id', ($authUser->getActiveInstitutionId() ?? null));
                break;
            case 'instructor':
                $academies = $authUser->academies;
                $clans = [];

                foreach ($academies as $academy) {
                    $schools = $academy->schools;
                    foreach ($schools as $school) {
                        foreach ($school->clan as $singleClan) {
                            $clans[] = $singleClan->id;
                        }
                    }
                }

                if ($clan->personnel->where('id', $authUser->id)->count() === 0 && !in_array($clan->id, $clans)) {
                    return redirect()->route('dashboard')->with('error', 'You are not authorized to access this page.');
                }

                $schools = $authUser->primaryAcademy() ? $authUser->primaryAcademy()->schools : [];
                break;
            default:
                return redirect()->route('dashboard')->with('error', 'You are not authorized to access this page.');
                $schools = [];
                break;
        }

        $formatted_schools = [
            [
                'value' => '',
                'label' => 'Select a school'
            ]
        ];

        foreach ($schools as $key => $school) {
            $formatted_schools[] = [
                'value' => $school->id,
                'label' => $school->name
            ];
        }

        $associated_instructors = $clan->personnel;
        $associated_athletes = $clan->users()->where('is_disabled', '0')->get();

        $instructors = User::where('is_disabled', '0')->whereHas('roles', function ($query) {
            $query->where('label', 'instructor');
        })->whereNotIn('id', $clan->personnel->pluck('id'))->get();

        $athletes = [];

        // Admin (tutti), rector (della sua accademia), manager (della sua accademia), dean (della sua scuola)
        // Poi se mancano delle associazioni con scuola e accademia, si aggiungono.
        switch ($authRole) {
            case 'admin':
                $athletes = User::where('is_disabled', '0')->whereHas('roles', function ($query) {
                    $query->where('label', 'athlete');
                })->whereNotIn('id', $clan->users->pluck('id'))->get();
                break;
            case 'rector':
            case 'manager':
                $academy = $clan->academy;
                $athletes = User::where('is_disabled', '0')->whereHas('roles', function ($query) {
                    $query->where('label', 'athlete');
                })->whereNotIn('id', $clan->users->pluck('id'))->whereHas('academyAthletes', function ($query) use ($academy) {
                    $query->where('academy_id', $academy->id);
                })->get();
                break;
            case 'dean':
                $school = $clan->school;
                $athletes = User::where('is_disabled', '0')->whereHas('roles', function ($query) {
                    $query->where('label', 'athlete');
                })->whereNotIn('id', $clan->users->pluck('id'))->whereHas('schoolAthletes', function ($query) use ($school) {
                    $query->where('school_id', $school->id);
                })->get();
                break;
        }

        foreach ($associated_instructors as $key => $person) {
            $associated_instructors[$key]->role = implode(', ', $person->roles->pluck('name')->map(function ($role) {
                return __('users.' . $role);
            })->toArray());
        }

        $roles = Role::all();
        $editable_roles = $authUser->getEditableRoles();

        $weaponForms = WeaponForm::all();
        $weaponForms->prepend((object)[
            'id' => 0,
            'name' => 'Other'
        ]);

        $viewPath = $authRole === 'admin' ? 'clan.edit' : 'clan.' . $authRole . '.edit';
        return view($viewPath, [
            'clan' => $clan,
            'schools' => $formatted_schools,
            'associated_instructors' => $associated_instructors,
            'instructors' => $instructors,
            'associated_athletes' => $associated_athletes,
            'athletes' => $athletes,
            'roles' => $roles,
            'editable_roles' => $editable_roles,
            'available_weapons' => $weaponForms,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Clan $clan) {
        //
        $authUser = User::find(auth()->user()->id);
        $authRole = $authUser->getRole();
        if (!in_array($authRole, ['admin', 'rector', 'dean', 'manager'])) {
            return redirect()->route('dashboard')->with('error', 'Not authorized to access this page.');
        }
        $request->validate([
            'name' => 'required',
            'school_id' => 'required',
        ]);

        if ($request->school_id != $clan->school_id) {
            $request->validate([
                'transfer_athletes' => 'required'
            ]);
            if (!in_array($authRole, ['admin', 'rector'])) {
                return redirect()->route('dashboard')->with('error', 'You are not authorized to move courses between schools.');
            }
        }

        switch ($authRole) {
            case 'admin':
                break;
            case 'rector':
            case 'manager':
                $primaryAcademy = $authUser->primaryAcademy();
                $oldSchool = $primaryAcademy ? $primaryAcademy->schools->where('id', $clan->school_id)->first() : null;
                $newSchool = $primaryAcademy ? $primaryAcademy->schools->where('id', $request->school_id)->first() : null;
                if (!$oldSchool || !$newSchool) {
                    return redirect()->route('dashboard')->with('error', 'You are not authorized to move courses between these schools.');
                }
                break;
            case 'dean':
                $school = $authUser->primarySchool();
                if (!$school || ($school->id != $clan->school_id) || ($school->id != $request->school_id)) {
                    return redirect()->route('dashboard')->with('error', 'You are not authorized to make this change.');
                }
                break;
            default:
                return redirect()->route('dashboard')->with('error', 'You are not authorized to make this change.');
                break;
        }

        if ($request->weapon_form_id == 0) {
            $request->weapon_form_id = null;
        }

        // Qunado viene modificata la scuola del corso, si devono gestire anche le associazioni con gli atleti e gli istruttori
        if ($request->school_id != $clan->school_id) {
            if (!in_array($request->transfer_athletes, ['yes', 'no'])) {
                return back()->with('error', 'Invalid value for transfer_athletes.');
            }
            Log::channel('clan')->info('Moving clan to another school', [
                'made_by' => $authUser->id,
                'clan' => $clan->id,
                'old_school' => $clan->school_id,
                'new_school' => $request->school_id,
                'old_academy' => $clan->school->academy->id ?? '',
                'new_academy' => School::find($request->school_id)->academy->id ?? '',
                'transfer_athletes' => $request->transfer_athletes,
                'clan_athletes' => $clan->users()->pluck('user_id')->toArray(),
                'clan_personnel' => $clan->personnel()->pluck('user_id')->toArray(),
            ]);
            if ($request->transfer_athletes == 'yes') {
                // La scuola cambia e si vogliono spostare anche gli atleti
                $oldSchool = School::find($clan->school_id);
                $newSchool = School::find($request->school_id);
                $athletes = $clan->users()->get();

                // Modificando prima la scuola del corso si mantengono le associazioni con gli atleti, che in questo caso non vanno eliminate
                $clan->update([
                    'school_id' => $request->school_id,
                ]);

                if ($oldSchool->academy->id != $newSchool->academy->id) {
                    // Cambia anche l'accademia, quindi si eliminano associazioni con accademie, scuole e corsi
                    foreach ($athletes as $athlete) {
                        // Mettiamo l'eccezione per evitare di rimuovere le associazioni col corso spostato.
                        $athlete->removeAcademiesAthleteAssociations($newSchool->academy);
                        $athlete->academyAthletes()->syncWithoutDetaching($newSchool->academy->id);
                        if (!$athlete->primaryAcademyAthlete()) {
                            $athlete->setPrimaryAcademyAthlete($newSchool->academy->id);
                        }
                        $athlete->schoolAthletes()->syncWithoutDetaching($newSchool->id);
                        Log::channel('user')->info('Athlete associated with academy - moving clan', [
                            'made_by' => $authUser->id,
                            'user' => $athlete->id,
                            'academy' => $newSchool->academy->id,
                            'school' => $newSchool->id,
                            'clan' => $clan->id,
                        ]);
                        Log::channel('user')->info('Athlete associated with school - moving clan', [
                            'made_by' => $authUser->id,
                            'user' => $athlete->id,
                            'school' => $newSchool->id,
                            'clan' => $clan->id,
                        ]);
                    }
                } else {
                    // L'accademia non cambia, quindi si aggiungono solo le associazioni con la nuova scuola
                    foreach ($athletes as $athlete) {
                        if (!$athlete->schoolAthletes->contains($newSchool->id)) {
                            $athlete->schoolAthletes()->syncWithoutDetaching($newSchool->id);
                            Log::channel('user')->info('Athlete associated with school - moving clan', [
                                'made_by' => $authUser->id,
                                'user' => $athlete->id,
                                'school' => $newSchool->id,
                                'clan' => $clan->id,
                            ]);
                        }
                    }
                }
            } else {
                // La scuola cambia e non si vogliono spostare gli atleti

                $clanAthletes = $clan->users;
                if ($clanAthletes->count() > 0) {
                    Log::channel('clan')->info('Removed athletes associations - moving clan no athletes', [
                        'made_by' => $authUser->id,
                        'athletes' => $clanAthletes->pluck('id')->toArray(),
                        'clan' => $clan->id,
                    ]);
                }
                foreach ($clanAthletes as $athlete) {
                    $clan->users()->detach($athlete->id);
                    Log::channel('user')->info('Removed athlete associations - moving clan no athletes', [
                        'made_by' => $authUser->id,
                        'athlete' => $athlete->id,
                        'clan' => $clan->id,
                    ]);
                }
            }

            // In ogni caso si rimuove il personale associato al corso

            $clanPersonnel = $clan->personnel;
            if ($clanPersonnel->count() > 0) {
                Log::channel('clan')->info('Removed personnel associations - moving clan', [
                    'made_by' => $authUser->id,
                    'personnel' => $clanPersonnel->pluck('id')->toArray(),
                    'clan' => $clan->id,
                ]);
            }
            foreach ($clanPersonnel as $person) {
                $clan->personnel()->detach($person->id);
                Log::channel('user')->info('Removed personnel associations - moving clan', [
                    'made_by' => $authUser->id,
                    'personnel' => $person->id,
                    'clans' => [$clan->id],
                ]);
            }
        }

        $clan->update([
            'name' => $request->name,
            'school_id' => $request->school_id,
            'slug' => Str::slug($request->name),
            'weapon_form_id' => $request->weapon_form_id
        ]);

        $redirectRoute = $authRole === 'admin' ? 'clans.edit' : $authRole . '.clans.edit';
        return redirect()->route($redirectRoute, $clan)->with('success', 'Course updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Clan $clan) {
        //
        $authUser = User::find(auth()->user()->id);
        $authRole = $authUser->getRole();
        if (!in_array($authRole, ['admin', 'rector', 'dean', 'manager'])) {
            return redirect()->route('dashboard')->with('error', 'Not authorized to access this page.');
        }

        // if($clan->users->count() > 0){
        //     return back()->with('error', 'Cannot delete course with associated athletes.');
        // }

        switch ($authRole) {
            case 'admin':
                break;
            case 'rector':
            case 'manager':
                $primaryAcademy = $authUser->primaryAcademy();
                if (!$primaryAcademy || !$primaryAcademy->schools->where('id', $clan->school_id)->first()) {
                    return redirect()->route('dashboard')->with('error', 'You are not authorized to delete this course.');
                }
                break;
            case 'dean':
                $school = $authUser->primarySchool();
                if (!$school || ($school->id != $clan->school_id)) {
                    return redirect()->route('dashboard')->with('error', 'You are not authorized to delete this course.');
                }
                break;
            default:
                return redirect()->route('dashboard')->with('error', 'You are not authorized to delete this course.');
                break;
        }

        $athletes = $clan->users()->pluck('user_id')->toArray();
        $personnel = $clan->personnel()->pluck('user_id')->toArray();

        $clan->users()->detach();
        $clan->personnel()->detach();
        $clan->is_disabled = true;
        $clan->save();

        Log::channel('clan')->info('Disabled clan', [
            'made_by' => $authUser->id,
            'clan' => $clan->id,
            'athletes_ids' => $athletes,
            'personnel_ids' => $personnel,
        ]);

        $redirectRoute = $authRole === 'admin' ? 'clans.index' : $authRole . '.clans.index';
        return redirect()->route($redirectRoute)->with('success', 'Course disabled successfully.');
    }

    public function addInstructor(Clan $clan, Request $request) {
        $authRole = User::find(auth()->user()->id)->getRole();
        if (!$this->checkPermission($clan)) {
            return redirect()->route('dashboard')->with('error', 'Not authorized.');
        }

        // Se mancano le associazioni a scuola e accademia del corso, si aggiungono
        $user = User::find($request->instructor_id);
        $school = School::find($clan->school_id);
        $academy = Academy::find($school->academy_id);
        $isInThisSchool = $school->personnel->where('id', $user->id)->count();
        $isInThisAcademy = $academy->personnel->where('id', $user->id)->count();
        if (!$isInThisAcademy) {
            $academy->personnel()->syncWithoutDetaching($user->id);
        }
        if (!$isInThisSchool) {
            $school->personnel()->syncWithoutDetaching($user->id);
        }

        $clan->personnel()->syncWithoutDetaching($request->instructor_id);

        // Se l'istruttore non ha l'accademia principale, la assegna
        if (!$user->primaryAcademy()) {
            $user->setPrimaryAcademy($academy->id);
        }
        // Se l'istruttore non ha la scuola principale, la assegna
        if (!$user->primarySchool()) {
            $user->setPrimarySchool($school->id);
        }

        $redirectRoute = $authRole === 'admin' ? 'clans.edit' : $authRole . '.clans.edit';
        return redirect()->route($redirectRoute, $clan)->with('success', 'Instructor added successfully.');
    }

    public function removeInstructor(Clan $clan, Request $request) {
        $authUser = User::find(auth()->user()->id);
        $authRole = $authUser->getRole();
        if (!$this->checkPermission($clan)) {
            return redirect()->route('dashboard')->with('error', 'Not authorized.');
        }

        $user = User::find($request->instructor_id);

        $user->removeClanPersonnelAssociations($clan);

        $redirectRoute = $authRole === 'admin' ? 'clans.edit' : $authRole . '.clans.edit';
        return redirect()->route($redirectRoute, $clan)->with('success', 'Instructor removed successfully.');
    }

    public function addAthlete(Clan $clan, Request $request) {
        $authUser = User::find(auth()->user()->id);
        $authRole = $authUser->getRole();
        if (!in_array($authRole, ['admin', 'rector', 'dean', 'manager', 'instructor'])) {
            return redirect()->route('dashboard')->with('error', 'Not authorized.');
        }

        // Se mancano le associazioni a scuola e accademia del corso, si aggiungono
        $user = User::find($request->athlete_id);
        $school = School::find($clan->school_id);
        $academy = Academy::find($school->academy_id);

        if (!$user->clans()->where('clan_id', $clan->id)->exists()) {
            if ($user->academyAthletes()->first()->id != $academy->id) {
                // L'admin può farlo sempre, il rettore, il dean e il manager solo se l'accademia è no academy
                if ($authRole !== 'admin' && $user->academyAthletes()->first()->id !== 1) {
                    return redirect()->route('dashboard')->with('error', 'Not authorized.');
                }
                // L'atleta può avere solo un'accademia associata
                $user->removeAcademiesAthleteAssociations();
            }

            $user->academyAthletes()->syncWithoutDetaching($academy->id);
            $user->schoolAthletes()->syncWithoutDetaching($school->id);
            $user->clans()->syncWithoutDetaching($clan->id);
        }

        $user->roles()->syncWithoutDetaching(Role::where('label', 'athlete')->first()->id);

        // Se l'atleta non ha l'accademia principale, la assegna
        if (!$user->primaryAcademyAthlete()) {
            $user->setPrimaryAcademyAthlete($academy->id);
        }
        // Se l'atleta non ha la scuola principale, la assegna
        if (!$user->primarySchoolAthlete()) {
            $user->setPrimarySchoolAthlete($school->id);
        }

        $redirectRoute = $authRole === 'admin' ? 'clans.edit' : $authRole . '.clans.edit';
        return redirect()->route($redirectRoute, $clan)->with('success', 'Athlete added successfully.');
    }

    public function removeAthlete(Clan $clan, Request $request) {
        $authUser = User::find(auth()->user()->id);
        $authRole = $authUser->getRole();
        if (!$this->checkPermission($clan)) {
            return redirect()->route('dashboard')->with('error', 'Not authorized.');
        }

        $user = User::find($request->athlete_id);

        $user->removeClanAthleteAssociations($clan);

        $redirectRoute = $authRole === 'admin' ? 'clans.edit' : $authRole . '.clans.edit';
        return redirect()->route($redirectRoute, $clan)->with('success', 'Athlete removed successfully.');
    }

    public function all(Request $request) {
        //

        $clans = Clan::orderBy('created_at', 'desc')->where('is_disabled', '0')->with(['school'])->get();
        $formatted_clans = [];

        foreach ($clans as $key => $clan) {
            $formatted_clans[] = [
                'id' => $clan->id,
                'school' => $clan->school->name,
                'name' => $clan->name
            ];
        }

        return response()->json($formatted_clans);
    }

    public function search(Request $request) {
        //

        $clans = Clan::query()->when($request->search, function ($q, $search) {
            return $q->whereIn('id', Clan::search($search)->keys())->where('is_disabled', '0');
        })->with(['school'])->get();

        $formatted_clans = [];

        foreach ($clans as $key => $clan) {
            $formatted_clans[] = [
                'id' => $clan->id,
                'school' => $clan->school->name,
                'name' => $clan->name
            ];
        }

        return response()->json($formatted_clans);
    }

    public function getBySchool(Request $request) {


        $schools = json_decode($request->schools);

        $clans = Clan::whereIn('school_id', $schools)->where('is_disabled', '0')->with(['school'])->get();
        $formatted_clans = [];

        foreach ($clans as $key => $clan) {
            $formatted_clans[] = [
                'id' => $clan->id,
                'school' => $clan->school->name,
                'name' => $clan->name
            ];
        }

        return response()->json($formatted_clans);
    }

    public function checkPermission(Clan $clan) {
        $authUser = User::find(auth()->user()->id);
        $authRole = $authUser->getRole();
        $permitted = false;
        switch ($authRole) {
            case 'admin':
                $permitted = true;
                break;
            case 'rector':
                $academy = $clan->academy;
                $permitted = ($authUser->getActiveInstitutionId() ?? null) == $academy->id;
                break;
            case 'manager':
                // $school = $clan->school;
                // $primarySchool = $authUser->primarySchool();
                // $permitted = $primarySchool && $school && ($primarySchool->id == $school->id);
                // Modificato per essere come il rettore. (ticket 3681)
                $academy = $clan->academy;
                $permitted = ($authUser->getActiveInstitutionId() ?? null) == $academy->id;
                break;
            case 'dean':
                $school = $clan->school;
                // $dean = $school->dean() ?? null;
                // $permitted = $dean && ($dean->id == $authUser->id);
                $permitted = ($authUser->getActiveInstitutionId() ?? null) == $school->id;
                break;
            default:
                break;
        }
        return $permitted;
    }
}
