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

        if (!$authUser->validatePrimaryInstitutionPersonnel()) {
            return redirect()->route('dashboard')->with('error', 'Not authorized.');
        }

        switch ($authRole) {
            case 'admin':
                $clans = Clan::orderBy('created_at', 'desc')->where('is_disabled', '0')->with(['school'])->get();
                break;
            case 'rector':
                $primaryAcademy = $authUser->primaryAcademy();
                $clans = Clan::where('is_disabled', '0')->whereIn('school_id', $primaryAcademy->schools->pluck('id'))->with(['school'])->get();
                break;
            case 'dean':
            case 'manager':
                $clans = $authUser->primarySchool() ? $authUser->primarySchool()->clan : [];
                break;
            case 'instructor':
                // Gli istruttori possono vedere tutti i corsi delle scuole a cui sono associati
                // $clans = $authUser->clansPersonnel;
                $clans = $authUser->schools->pluck('clan')->flatten();
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
                $schools = $authUser->primaryAcademy() ? $authUser->primaryAcademy()->schools : [];
                break;
            case 'dean':
            case 'manager':
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
            case 'manager':
                $school = $authUser->primarySchool() ?? null;
                if ($school && ($school->id != $request->school_id)) {
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

        $slug = Str::slug($request->name);

        if (Clan::where('slug', $slug)->exists()) {
            $slug = $slug . '-' . time();
        }

        $clan = Clan::create([
            'name' => $request->name,
            'school_id' => $request->school_id,
            'slug' => $slug

        ]);

        $authRole = User::find(auth()->user()->id)->getRole();

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
        if ($clan->is_disabled && $authRole !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Course disabled.');
        }
        switch ($authRole) {
            case 'admin':
                $schools = School::all();
                break;
            case 'rector':
                $school = $authUser->primaryAcademy() ? $authUser->primaryAcademy()->schools->where('id', $clan->school_id)->first() : null;
                if (!$school) {
                    return redirect()->route('dashboard')->with('error', 'You are not authorized to access this page.');
                }
                $schools = $authUser->primaryAcademy()->schools;
                break;
            case 'dean':
            case 'manager':
                if ($clan->school_id !== ($authUser->primarySchool()->id ?? null)) {
                    return redirect()->route('dashboard')->with('error', 'You are not authorized to access this page.');
                }
                $schools = $authUser->schools->where('id', ($authUser->primarySchool()->id ?? null));
                break;
            case 'instructor':
                if ($clan->personnel->where('id', $authUser->id)->count() === 0 && !in_array($clan->school_id, $authUser->schools->pluck('id')->toArray())) {
                    return redirect()->route('dashboard')->with('error', 'You are not authorized to access this page.');
                }
                // if($clan->personnel->where('id', $authUser->id)->count() === 0 && in_array($clan->school_id, $authUser->schools->pluck('id')->toArray())){
                // Qui si può decidere cosa fare in caso di istruttore che non è associato al corso ma è associato alla scuola 
                // }
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

        // Possono vedere tutti gli utenti e poi, se mancano delle associazioni con scuola e accademia, si aggiungono.
        $instructors = User::where('is_disabled', '0')->whereHas('roles', function ($query) {
            $query->where('label', 'instructor');
        })->whereNotIn('id', $clan->personnel->pluck('id'))->get();
        $athletes = User::where('is_disabled', '0')->whereHas('roles', function ($query) {
            $query->where('label', 'athlete');
        })->whereNotIn('id', $clan->users->pluck('id'))->get();

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

        switch ($authRole) {
            case 'admin':
                break;
            case 'rector':
                $primaryAcademy = $authUser->primaryAcademy();
                $oldSchool = $primaryAcademy ? $primaryAcademy->schools->where('id', $clan->school_id)->first() : null;
                $newSchool = $primaryAcademy ? $primaryAcademy->schools->where('id', $request->school_id)->first() : null;
                if (!$oldSchool || !$newSchool) {
                    return redirect()->route('dashboard')->with('error', 'You are not authorized to access this page.');
                }
                break;
            case 'dean':
            case 'manager':
                $school = $authUser->primarySchool();
                if (!$school || $school->id != $clan->school_id || $school->id != $request->school_id) {
                    return redirect()->route('dashboard')->with('error', 'You are not authorized to access this page.');
                }
                break;
            default:
                return redirect()->route('dashboard')->with('error', 'You are not authorized to access this page.');
                break;
        }

        if ($request->weapon_form_id == 0) {
            $request->weapon_form_id = null;
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
        if (!in_array($authRole, ['admin', 'rector', 'dean', 'manager'])) {
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

    public function addAthlete(Clan $clan, Request $request) {
        $authRole = User::find(auth()->user()->id)->getRole();
        if (!in_array($authRole, ['admin', 'rector', 'dean', 'manager', 'instructor'])) {
            return redirect()->route('dashboard')->with('error', 'Not authorized.');
        }

        // Se mancano le associazioni a scuola e accademia del corso, si aggiungono
        $user = User::find($request->athlete_id);
        $school = School::find($clan->school_id);
        $academy = Academy::find($school->academy_id);
        $isInThisSchool = $school->athletes->where('id', $user->id)->count();
        $isInThisAcademy = $academy->athletes->where('id', $user->id)->count();
        if (!$isInThisAcademy) {
            $academy->athletes()->syncWithoutDetaching($user->id);
            if ($user->academyAthletes()->count() > 1) {
                $noAcademy = Academy::where('slug', 'no-academy')->first();
                $noAcademy->athletes()->detach($user->id);
            }
        }
        if (!$isInThisSchool) {
            $school->athletes()->syncWithoutDetaching($user->id);
        }

        $clan->users()->syncWithoutDetaching($request->athlete_id);

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
}
