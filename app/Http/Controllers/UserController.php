<?php

namespace App\Http\Controllers;

use App\Mail\CreatedUserEmail;
use App\Models\Academy;
use App\Models\Announcement;
use App\Models\Clan;
use App\Models\Invoice;
use App\Models\Language;
use App\Models\Nation;
use App\Models\Rank;
use App\Models\Role;
use App\Models\School;
use App\Models\User;
use Carbon\Carbon;
use GPBMetadata\Google\Api\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class UserController extends Controller {

    public function index() {
        // Qui si dovrebbero filtrare gli utenti visualizzabili in base al ruolo dell'utente loggato.
        // Es. tutti vedono tutto tranne gli istruttori che sono limitati alle scuole in cui hanno un corso
        $authUser = User::find(auth()->user()->id);
        $authUserRole = $authUser->getRole();

        if (!in_array($authUserRole, ['admin', 'rector', 'dean', 'manager', 'technician', 'instructor'])) {
            return redirect()->route("dashboard")->with('error', 'You do not have the required role to access this page!');
        }

        if (!$authUser->validatePrimaryInstitutionPersonnel()) {
            return redirect()->route("dashboard")->with('error', 'You are not authorized to access this page!');
        }

        $roles = Role::all();
        $users_sorted_by_role = [];
        foreach ($roles as $role) {

            $users = [];

            foreach ($role->users as $user) {
                if ($user->is_disabled) {
                    continue;
                }

                // admin, rector, dean, manager e technician possono vedere tutti gli utenti.
                // instructor può vedere solo gli utenti delle scuole in cui ha corsi in cui è associato come personale.
                if ($authUserRole === 'instructor') {
                    $authSchools = auth()->user()->schools->pluck('id')->toArray();
                    if (
                        $user->schools->whereIn('id', $authSchools)->isEmpty()
                        && $user->schoolAthletes->whereIn('id', $authSchools)->isEmpty()
                    ) {
                        continue;
                    }
                }

                if ($role->label === 'athlete') {
                    $user->academy = $user->primaryAcademyAthlete();
                    $user->school = $user->primarySchoolAthlete();
                    if ($user->academy) {
                        $user->nation = $user->academy->nation->name;
                    } else {

                        if ($user->nation_id === null) {
                            $user->nation = "Not set";
                        } else {
                            $nation = Nation::find($user->nation_id);
                            $user->nation = $nation->name;
                        }
                    }
                }

                $users[] = $user;
            }

            $users_sorted_by_role[$role->label] = $users;
        }

        $viewPath = $authUserRole === 'admin' ? 'users.index' : 'users.' . $authUserRole . '.index';

        return view($viewPath, [
            'users' => $users_sorted_by_role,
            'roles' => $roles,
        ]);
    }

    public function create() {
        $authUser = User::find(auth()->user()->id);
        $authRole = $authUser->getRole();

        if (!in_array($authRole, ['admin', 'rector', 'dean', 'manager'])) {
            return back()->with('error', 'You do not have the required role to access this page!');
        }

        if (!$authUser->validatePrimaryInstitutionPersonnel()) {
            return redirect()->route("dashboard")->with('error', 'You are not authorized to access this page!');
        }

        $roles = $authUser->getEditableRoles();

        switch ($authRole) {
            case 'admin':
                $academies = Academy::where('is_disabled', false)->get();
                break;
            case 'rector':
                $primaryAcademy = $authUser->primaryAcademy();
                $academies = collect([$primaryAcademy]);
                break;
            case 'dean':
                $academies = Academy::where('is_disabled', false)->where('id', ($authUser->primarySchool()->academy->id ?? null))->get();
                break;
            case 'manager':
                $academies = Academy::where('is_disabled', false)->where('id', ($authUser->primarySchool()->academy->id ?? null))->get();
                break;
            default:
                return back()->with('error', 'You do not have the required role to access this page!');
        }

        $viewPath = $authRole === 'admin' ? 'users.create' :  'users.' . $authRole . '.create';

        return view($viewPath, [
            'roles' => $roles,
            'academies' => $academies,
        ]);
    }

    public function store(Request $request) {
        $authUser = User::find(auth()->user()->id);
        $authRole = $authUser->getRole();

        if (!in_array($authRole, ['admin', 'rector', 'dean', 'manager'])) {
            return back()->with('error', 'You do not have the required role to access this page!');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
        ]);


        $nation = Nation::where('name', $request->nationality)->first();
        $user = User::create([
            'name' => $request->name,
            'surname' => $request->surname,
            'email' => $request->email,
            'password' => bcrypt(Str::random(10)),
            'subscription_year' => $request->year,
            'academy_id' => $request->academy_id ?? 1,
            'nation_id' => $nation->id,
            // 'unique_code' => $unique_code,
        ]);

        $roles = explode(',', $request->roles);

        foreach ($roles as $role) {
            if (!$authUser->canModifyRole($role)) {
                continue;
            }
            $roleElement = Role::where('label', $role)->first();

            if ($roleElement) {
                $user->roles()->syncWithoutDetaching($roleElement->id);
            }
        }

        $academy = Academy::find($request->academy_id ?? 1);

        if ($user->hasRole('athlete')) {
            $academy->athletes()->syncWithoutDetaching($user->id);
            $user->setPrimaryAcademyAthlete($academy->id);
        } else {
            $academy->personnel()->syncWithoutDetaching($user->id);
            $user->setPrimaryAcademy($academy->id);
        }

        foreach ($user->allowedRoles() as $role) {
            if (in_array($role, ['rector', 'dean', 'instructor', 'manager', 'technician'])) {
                $academy->personnel()->syncWithoutDetaching($user->id);
                if (!$user->primaryAcademy()) {
                    $user->setPrimaryAcademy($academy->id);
                }
                break;
            }
        }

        Mail::to($user->email)
            ->send(new CreatedUserEmail($user));

        $redirectRoute = $authRole === 'admin' ? 'users.edit' :  $authRole . '.users.edit';
        return redirect()->route($redirectRoute, $user)->with('success', 'User created successfully!');
    }

    public function storeForAcademy(Request $request) {
        $authUser = User::find(auth()->user()->id);
        $authRole = $authUser->getRole();

        if (!in_array($authRole, ['admin', 'rector', 'dean', 'manager'])) {
            return back()->with('error', 'You do not have the required role to access this page!');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'surname' => 'required|string|max:255',
        ]);


        $code_valid = false;

        while (!$code_valid) {
            $unique_code = Str::random(4) . "-" . Str::random(4) . "-" . Str::random(4) . "-" . Str::random(4);
            $code_valid = User::where('unique_code', $unique_code)->count() == 0;
        }

        $academy = Academy::find($request->academy_id);

        $user = User::create([
            'name' => $request->name,
            'surname' => $request->surname,
            'email' => $request->email,
            'password' => bcrypt(Str::random(10)),
            'subscription_year' => date('Y'),
            'academy_id' => $academy->id,
            'nation_id' => $academy->nation->id,
            'unique_code' => $unique_code,
        ]);


        if ($request->type == "athlete") {

            $role = Role::where('label', 'athlete')->first();
            $user->roles()->syncWithoutDetaching($role->id);
            $academy->athletes()->syncWithoutDetaching($user->id);
            $user->setPrimaryAcademyAthlete($academy->id);
        } else {

            $roles = explode(',', $request->roles);

            foreach ($roles as $role) {
                if (!$authUser->canModifyRole($role)) {
                    continue;
                }
                $roleElement = Role::where('label', $role)->first();
                if ($roleElement) {
                    $user->roles()->syncWithoutDetaching($roleElement->id);
                }
            }
            $academy->personnel()->syncWithoutDetaching($user->id);
            $user->setPrimaryAcademy($academy->id);
        }


        Mail::to($user->email)
            ->send(new CreatedUserEmail($user));


        if ($request->go_to_edit === 'on') {
            $redirectRoute = $authRole === 'admin' ? 'users.edit' : $authRole . '.users.edit';
            return redirect()->route($redirectRoute, $user->id)->with('success', 'User created successfully!');
        } else {
            $redirectRoute = $authRole === 'admin' ? 'academies.edit' : $authRole . '.academies.edit';
            return redirect()->route($redirectRoute, $academy->id)->with('success', 'User created successfully!');
        }
    }

    public function  storeForSchool(Request $request) {
        $authUser = User::find(auth()->user()->id);
        $authRole = $authUser->getRole();

        if (!in_array($authRole, ['admin', 'rector', 'dean', 'manager'])) {
            return back()->with('error', 'You do not have the required role to access this page!');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'surname' => 'required|string|max:255',
        ]);

        $code_valid = false;

        while (!$code_valid) {
            $unique_code = Str::random(4) . "-" . Str::random(4) . "-" . Str::random(4) . "-" . Str::random(4);
            $code_valid = User::where('unique_code', $unique_code)->count() == 0;
        }

        $school = School::find($request->school_id);
        $academy = $school->academy;

        $user = User::create([
            'name' => $request->name,
            'surname' => $request->surname,
            'email' => $request->email,
            'password' => bcrypt(Str::random(10)),
            'subscription_year' => date('Y'),
            'academy_id' => $academy->id,
            'nation_id' => $academy->nation->id,
            'school_id' => $school->id,
            'unique_code' => $unique_code,
        ]);

        if ($request->type == "athlete") {

            $role = Role::where('label', 'athlete')->first();
            $user->roles()->syncWithoutDetaching($role->id);
            $academy->athletes()->syncWithoutDetaching($user->id);
            $school->athletes()->syncWithoutDetaching($user->id);
            $user->setPrimaryAcademyAthlete($academy->id);
            $user->setPrimarySchoolAthlete($school->id);
        } else {

            $roles = explode(',', $request->roles);

            foreach ($roles as $role) {
                if (!$authUser->canModifyRole($role)) {
                    continue;
                }
                $roleElement = Role::where('label', $role)->first();
                if ($roleElement) {
                    $user->roles()->syncWithoutDetaching($roleElement->id);
                }
            }
            $academy->personnel()->syncWithoutDetaching($user->id);
            $school->personnel()->syncWithoutDetaching($user->id);
            $user->setPrimaryAcademy($academy->id);
            $user->setPrimarySchool($school->id);
        }


        Mail::to($user->email)
            ->send(new CreatedUserEmail($user));


        if ($request->go_to_edit === 'on') {
            $redirectRoute = $authRole === 'admin' ? 'users.edit' : $authRole . '.users.edit';
            return redirect()->route($redirectRoute, $user->id)->with('success', 'User created successfully!');
        } else {
            $redirectRoute = $authRole === 'admin' ? 'schools.edit' : $authRole . '.schools.edit';
            return redirect()->route($redirectRoute, $school->id)->with('success', 'User created successfully!');
        }
    }

    public function  storeForClan(Request $request) {
        $authUser = User::find(auth()->user()->id);
        $authRole = $authUser->getRole();

        if (!in_array($authRole, ['admin', 'rector', 'dean', 'manager'])) {
            return back()->with('error', 'You do not have the required role to access this page!');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'surname' => 'required|string|max:255',
        ]);

        $code_valid = false;

        while (!$code_valid) {
            $unique_code = Str::random(4) . "-" . Str::random(4) . "-" . Str::random(4) . "-" . Str::random(4);
            $code_valid = User::where('unique_code', $unique_code)->count() == 0;
        }

        $clan = Clan::find($request->clan_id);
        $school = School::find($clan->school->id);
        $academy = $school->academy;

        $user = User::create([
            'name' => $request->name,
            'surname' => $request->surname,
            'email' => $request->email,
            'password' => bcrypt(Str::random(10)),
            'subscription_year' => date('Y'),
            'academy_id' => $academy->id,
            'nation_id' => $academy->nation->id,
            'school_id' => $school->id,
            'unique_code' => $unique_code,
        ]);

        if ($request->type == "athlete") {

            $role = Role::where('label', 'athlete')->first();
            $user->roles()->syncWithoutDetaching($role->id);
            $academy->athletes()->syncWithoutDetaching($user->id);
            $school->athletes()->syncWithoutDetaching($user->id);
            $clan->users()->syncWithoutDetaching($user->id);
            $user->setPrimaryAcademyAthlete($academy->id);
            $user->setPrimarySchoolAthlete($school->id);
        } else {

            $roles = explode(',', $request->roles);

            foreach ($roles as $role) {
                if (!$authUser->canModifyRole($role)) {
                    continue;
                }
                $roleElement = Role::where('label', $role)->first();
                if ($roleElement) {
                    $user->roles()->syncWithoutDetaching($roleElement->id);
                }
            }
            $academy->personnel()->syncWithoutDetaching($user->id);
            $school->personnel()->syncWithoutDetaching($user->id);
            $clan->personnel()->syncWithoutDetaching($user->id);
            $user->setPrimaryAcademy($academy->id);
            $user->setPrimarySchool($school->id);
        }


        Mail::to($user->email)
            ->send(new CreatedUserEmail($user));

        if ($request->go_to_edit === 'on') {
            $redirectRoute = $authRole === 'admin' ? 'users.edit' : $authRole . '.users.edit';
            return redirect()->route($redirectRoute, $user->id)->with('success', 'User created successfully!');
        } else {
            $redirectRoute = $authRole === 'admin' ? 'clans.edit' : $authRole . '.clans.edit';
            return redirect()->route($redirectRoute, $request->clan_id)->with('success', 'User created successfully!');
        }
    }

    public function edit(User $user) {

        $authRole = User::find(auth()->user()->id)->getRole();
        if (!in_array($authRole, ['admin', 'rector', 'dean', 'manager'])) {
            return back()->with('error', 'You do not have the required role to access this page!');
        }

        $nations = Nation::all();

        foreach ($nations as $nation) {
            $countries[$nation['continent']][] = ['id' => $nation['id'], 'name' => $nation['name']];
        }

        $countries = [
            'Europe' => $countries['Europe'],
            'Africa' => $countries['Africa'],
            'Asia' => $countries['Asia'],
            'North America' => $countries['North America'],
            'South America' => $countries['South America'],
            'Oceania' => $countries['Oceania'],
        ];

        if ($user->academy) {
            $academy = Academy::find($user->academy->id);
            $schools = $academy->schools;
        } else {
            $schools = [];
        }

        $user->is_verified = $user->email_verified_at ? true : false;

        $roles = Role::all();
        $user->roles = $user->roles->pluck('label')->toArray();

        if ($user->profile_picture !== null) {
            /** 
             * @disregard Intelephense non rileva il metodo temporaryurl
             * 
             * @see https://github.com/spatie/laravel-google-cloud-storage
             */
            $user->profile_picture = Storage::disk('gcs')->temporaryUrl(
                $user->profile_picture,
                now()->addMinutes(5)
            );
        }

        $allLanguages = Language::all();

        $authRole = User::find(auth()->user()->id)->getRole();
        $viewPath = $authRole === 'admin' ? 'users.edit' :  'users.' . $authRole . '.edit';

        return view($viewPath, [
            'user' => $user,
            'academies' => $user->nation->academies ?? [],
            'schools' => $schools,
            'nations' => $countries,
            'roles' => $roles,
            'languages' => $allLanguages,
        ]);
    }

    public function show(User $user) {

        $roles = Role::all();
        $user->roles = $user->roles->pluck('label')->toArray();

        if (!$user->nation()->exists()) {
            $user->nation = Nation::find(2);
        }

        if (!$user->rank()->exists()) {
            $user->rank = Rank::find(1);
        }

        // Risultati eventi 

        $events_user = $user->eventResults()->with('event')->get();
        $events_formatted = [];
        $participated_events = [];

        foreach ($events_user as $event) {

            if (in_array($event->event->id, $participated_events)) {
                continue;
            }

            $event_date = Carbon::parse($event->event->start_date);

            $events_formatted[] = [
                'event' => $event->event->name,
                'war_points' => $event->total_war_points,
                'style_points' => $event->total_style_points,
                'date' => $event_date->format('d/m/Y'),
                'placement' => 32 - $event->war_points + 1,
            ];

            $participated_events[] = $event->event->id;
        }

        $user->events = $events_formatted;

        return view('website.user-show', [
            'user' => $user,
            'roles' => $roles,
        ]);
    }

    public function propic(User $user) {

        $cacheKey = 'propic-' . $user->id;

        $image = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($user) {
            if ($user->profile_picture !== null) {
                /** 
                 * @disregard Intelephense non rileva il metodo temporaryurl
                 * 
                 * @see https://github.com/spatie/laravel-google-cloud-storage
                 */
                $url = Storage::disk('gcs')->temporaryUrl(
                    $user->profile_picture,
                    now()->addMinutes(5)
                );
            } else {
                $url = 'https://ui-avatars.com/api/?name=' . $user->name . '+' . $user->surname . '&size=256';
            }

            $response = Http::get($url);

            return $response->body();
        });

        $headers = [
            'Content-Type' => 'image/png',
            'Content-Length' => strlen($image),
        ];

        return response($image, 200, $headers);
    }

    public function update(Request $request, User $user) {
        $authRole = User::find(auth()->user()->id)->getRole();

        if (!in_array($authRole, ['admin', 'rector', 'dean', 'manager'])) {
            return back()->with('error', 'You do not have the required role to access this page!');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'year' => 'required|integer',
            'nationality' => 'required|string|exists:nations,id',
        ]);


        $user->update([
            'name' => $request->name,
            'surname' => $request->surname,
            'email' => $request->email,
            'subscription_year' => $request->year,
            'nation_id' => $request->nationality,
        ]);

        // Recupera i ruoli attuali dell'utente
        $currentRoles = $user->roles->pluck('label')->toArray();
        // Recupera i nuovi ruoli dal request
        $newRoles = explode(',', $request->roles);

        // Recupera l'utente che ha fatto la richiesta
        $authUser = User::find(auth()->user()->id);

        // Determina i ruoli da aggiungere e da rimuovere
        $rolesToAdd = array_diff($newRoles, $currentRoles);
        $rolesToRemove = array_diff($currentRoles, $newRoles);

        $shouldLogRolesChange = false;

        // Check if $rolesToAdd has any items
        if (!empty($rolesToAdd)) {
            $shouldLogRolesChange = true;
            foreach ($rolesToAdd as $roleLabel) {
                $roleElement = Role::where('label', $roleLabel)->first();
                if ($roleElement && $authUser->canModifyRole($roleLabel)) {
                    $user->roles()->syncWithoutDetaching($roleElement->id);
                }
            }
        }

        // Check if $rolesToRemove has any items
        if (!empty($rolesToRemove)) {
            $shouldLogRolesChange = true;
            foreach ($rolesToRemove as $roleLabel) {
                $roleElement = Role::where('label', $roleLabel)->first();
                if ($roleElement && $authUser->canModifyRole($roleLabel)) {
                    $user->roles()->detach($roleElement->id);
                }
            }
        }

        if ($shouldLogRolesChange) {
            Log::channel('role')->info('Roles changed', [
                'user' => $user->id,
                'made_by' => $authUser->id,
                'old_roles' => $currentRoles,
                'roles' => $newRoles,
            ]);
        }


        $authUserRole = User::find(auth()->user()->id)->getRole();
        $redirectRoute = $authUserRole === 'admin' ? 'users.edit' :  $authUserRole . '.users.edit';

        return redirect()->route($redirectRoute, $user->id)->with('success', 'User updated successfully!');
    }

    public function destroy(User $user) {
        $authRole = User::find(auth()->user()->id)->getRole();

        if (!in_array($authRole, ['admin', 'rector', 'dean', 'manager'])) {
            return back()->with('error', 'You do not have the required role to access this page!');
        }

        $user->is_disabled = true;
        $user->save();

        $redirectRoute = $authRole === 'admin' ? 'users.index' :  $authRole . '.users.index';
        return redirect()->route($redirectRoute)->with('success', 'User disabled successfully!');
    }

    public function search(Request $request) {

        $request->validate([
            'search' => 'required|string',
        ]);

        // Installare meilisearch e continuare aggiungendo le condizionni per rector, dean, manager ecc.

        $authRole = User::find(auth()->user()->id)->getRole();

        if (in_array($authRole, ['admin', 'rector', 'dean', 'manager', 'technician'])) {
            $users = User::query()
                ->when($request->search, function (Builder $q, $value) {
                    /** 
                     * @disregard Intelephense non rileva il metodo whereIn
                     */
                    return $q->whereIn('id', User::search($value)->keys());
                })->with(['roles', 'academies', 'academyAthletes', 'nation'])->get();
        } else if ($authRole == 'instructor') {
            $users = User::query()
                ->when($request->search, function (Builder $q, $value) {
                    /** 
                     * @disregard Intelephense non rileva il metodo whereIn
                     */
                    return $q->whereIn('users.id', User::search($value)->keys());
                })->where(function ($query) {
                    $query->whereHas('schools', function ($q) {
                        return $q->whereIn('schools.id', auth()->user()->schools->pluck('id'));
                    })->orWhereHas('schoolAthletes', function ($q) {
                        return $q->whereIn('schools.id', auth()->user()->schools->pluck('id'));
                    });
                })->with(['roles', 'academies', 'academyAthletes', 'nation'])->get();
        }

        $viewPath = $authRole === 'admin' ? 'users.search-result' :  'users.' . $authRole . '.search-result';
        return view($viewPath, [
            'users' => $users,
        ]);
    }

    public function searchJson(Request $request) {

        $request->validate([
            'search' => 'required|string',
        ]);

        $users = User::query()
            ->when($request->search, function (Builder $q, $value) {
                /** 
                 * @disregard Intelephense non rileva il metodo whereIn
                 */
                return $q->whereIn('id', User::search($value)->keys());
            })->with(['roles', 'academies', 'academyAthletes', 'nation'])->get();

        return response()->json($users);
    }

    public function filter() {
        $authUser = User::find(auth()->user()->id);
        $authRole = $authUser->getRole();

        switch ($authRole) {
            case 'admin':
            case 'rector':
            case 'dean':
            case 'manager':
            case 'technician':
                $academies = Academy::where('is_disabled', false)->with('nation')->get();
                break;
                // case 'rector':
                //     $academies = auth()->user()->academies;
                //     break;
                // case 'dean':
                // case 'manager':
                //     $academies = collect((auth()->user()->primarySchool()->academy ?? null) ? [auth()->user()->primarySchool()->academy] : []);
                //     break;
                // case 'technician':
                //     $academies = Academy::where('is_disabled', false)->with('nation')->get();
                //     break;
            case 'instructor':
                $academies = collect([$authUser->primaryAcademy()]);
                break;
            default:
                return back()->with('error', 'You do not have the required role to access this page!');
        }

        $viewPath = $authRole === 'admin' ? 'users.filter' :  'users.' . $authRole . '.filter';
        return view($viewPath, [
            'academies' => $academies,
        ]);
    }

    public function filterResult(Request $request) {
        $authUser = User::find(auth()->user()->id);
        $authUserRole = $authUser->getRole();

        $users = [];

        // Stabilire il tipo di precisione 

        if (strlen($request->selectedCoursesJson) > 0) {
            $selectedCourses = json_decode($request->selectedCoursesJson);


            foreach ($selectedCourses as $course) {
                $course = Clan::find($course);

                foreach ($course->users as $user) {
                    $users[] = $user;
                }

                foreach ($course->personnel as $person) {
                    $users[] = $person;
                }
            }
        } else {

            if (strlen($request->selectedSchoolsJson) > 0) {
                $selectedSchools = json_decode($request->selectedSchoolsJson);

                foreach ($selectedSchools as $school) {
                    $school = School::find($school);

                    foreach ($school->athletes as $user) {
                        $users[] = $user;
                    }

                    foreach ($school->personnel as $person) {
                        $users[] = $person;
                    }
                }
            } else {
                if (strlen($request->selectedAcademiesJson) > 0) {
                    $selectedAcademies = json_decode($request->selectedAcademiesJson);

                    foreach ($selectedAcademies as $academy) {
                        $academy = Academy::find($academy);

                        foreach ($academy->athletes as $user) {
                            $users[] = $user;
                        }

                        foreach ($academy->personnel as $person) {
                            $users[] = $person;
                        }
                    }
                } else {

                    // Applica solo gli altri filtri 
                    $users = User::where('is_disabled', false)->get();
                }
            }
        }

        $shouldCheckForYear = $request->year != null;
        $shouldCheckForCreationDateFrom = $request->from != null;
        $shouldCheckForCreationDateTo = $request->to != null;

        // Serve solo per l'istruttore. elenco delle scuole in cui ha un corso.
        $authSchools = $authUser->schools->pluck('id')->toArray();

        $filteredUsers = [];

        foreach ($users as $user) {
            $shouldAdd = true;

            // L'istruttore può vedere solo gli utenti delle scuole in cui ha un corso.
            if ($authUserRole === 'instructor') {
                if (
                    $user->schools->whereIn('id', $authSchools)->isEmpty()
                    && $user->schoolAthletes->whereIn('id', $authSchools)->isEmpty()
                ) {
                    $shouldAdd = false;
                }
            }

            if ($shouldCheckForYear) {
                if ($user->subscription_year != $request->year) {
                    $shouldAdd = false;
                }
            }

            if ($shouldCheckForCreationDateFrom) {
                if ($user->created_at < $request->from) {
                    $shouldAdd = false;
                }
            }

            if ($shouldCheckForCreationDateTo) {
                if ($user->created_at > $request->to) {
                    $shouldAdd = false;
                }
            }

            if ($shouldAdd) {
                $filteredUsers[] = $user;
            }
        }

        foreach ($filteredUsers as $user) {

            $user->academy = $user->primaryAcademyAthlete();
            $user->school = $user->primarySchoolAthlete();
            if ($user->academy) {
                $user->nation = $user->academy->nation->name;
            } else {
                $nation = Nation::find($user->nation_id);
                $user->nation = $nation->name;
            }

            $user->role = implode(', ', $user->roles->pluck('name')->map(function ($role) {
                return __('users.' . $role);
            })->toArray());
        }

        $viewPath = $authUserRole === 'admin' ? 'users.filter-result' :  'users.' . $authUserRole . '.filter-result';
        return view($viewPath, [
            'users' => $filteredUsers,
        ]);
    }

    public function setUserRoleForSession(Request $request) {
        $request->validate([
            'role' => 'required|string|exists:roles,label',
        ]);

        $authUser = User::find(auth()->user()->id);

        if ($authUser->hasRole($request->role)) {
            session(['role' => $request->role]);
        } else {
            return back()->with('error', 'You do not have the required role to access this page!');
        }

        return redirect()->route('dashboard');
    }

    public function picture($id, Request $request) {
        $authRole = $request->user()->getRole();
        $redirectRoute = $authRole === 'admin' ? 'users.edit' :  $authRole . '.users.edit';
        if ($request->file('profilepicture') != null) {

            $file = $request->file('profilepicture');

            $file_extension = $file->getClientOriginalExtension();
            $file_name = time() . '_avatar.' . $file_extension;
            $path = "users/" . $id . "/" . $file_name;
            $storeFile = $file->storeAs("users/" . $id . "/", $file_name, "gcs");



            if ($storeFile) {
                $user = User::find($id);
                $user->profile_picture = $path;
                $user->save();

                return redirect()->route($redirectRoute, $user->id)->with('success', 'Profile picture uploaded successfully!');
            } else {
                return redirect()->route($redirectRoute, $id)->with('error', 'Error uploading profile picture!');
            }
        } else {
            return redirect()->route($redirectRoute, $id)->with('error', 'Error uploading profile picture!');
        }
    }

    public function userUploadPicture($id, Request $request) {

        if ($request->file('profilepicture') != null) {
            $file = $request->file('profilepicture');



            $file_extension = $file->getClientOriginalExtension();
            $file_name = time() . '_avatar.' . $file_extension;
            $path = "users/" . $id . "/" . $file_name;
            $storeFile = $file->storeAs("users/" . $id . "/", $file_name, "gcs");

            if ($storeFile) {
                $user = User::find($id);
                $user->profile_picture = $path;
                $user->save();

                return redirect()->route('profile.edit', $user->id)->with('success', 'Profile picture uploaded successfully!');
            } else {
                ddd($storeFile);
            }
        } else {
            return redirect()->route('profile.edit', $id)->with('error', 'Error uploading profile picture!');
        }
    }

    public function dashboard(Request $request) {
        $user = auth()->user()->id;
        $user = User::find($user);
        $role = $user->getRole();

        // Modificato per poter poi aggiungere altri ruoli
        switch ($role) {
            case 'instructor':
                if (isset($request->course_id)) {
                    return $this->handleInstructor($user, $request->course_id);
                } else {
                    return $this->handleInstructor($user, 0);
                }
            case 'athlete':
                return $this->handleAthlete($user);
            default:
                $view = 'dashboard.' . $role . '.index';
                return view($view);
        }
    }

    private function handleInstructor($user, $course_id = 0) {
        if ($course_id != 0) {
            $course = Clan::find($course_id);
            $users = $course->users;

            $active_users_count = 0;
            $inactive_users_count = 0;

            foreach ($users as $key => $atl) {
                $users[$key]->course_name = $course->name;

                if (!$atl->has_paid_fee) {
                    $inactive_users_count++;
                } else {
                    $active_users_count++;
                }
            }


            return view('dashboard.instructor.index', [
                'users' => $users,
                'courses' => $user->clansPersonnel()->get(),
                'course_id' => $course->id,
                'active_users_count' => $active_users_count,
                'inactive_users_count' => $inactive_users_count
            ]);
        } else {
            $courses = $user->clansPersonnel()->get();
            $athletes = [];
            $athletes_ids = [];

            $active_users_count = 0;
            $inactive_users_count = 0;

            foreach ($courses as $course) {
                foreach ($course->users as $athlete) {

                    if (!in_array($athlete->id, $athletes_ids)) {
                        $athletes_ids[] = $athlete->id;

                        if (!$athlete->has_paid_fee) {
                            $inactive_users_count++;
                        } else {
                            $active_users_count++;
                        }

                        $athlete->course_name = $course->name;
                        $athletes[] = $athlete;
                    } else {

                        $athlete = $athletes[array_search($athlete->id, array_column($athletes, 'id'))];
                        $athlete->course_name .= ", " . $course->name;

                        continue;
                    }
                }
            }


            return view('dashboard.instructor.index', [
                'courses' => $courses,
                'users' => $athletes,
                'course_id' => 0,
                'active_users_count' => $active_users_count,
                'inactive_users_count' => $inactive_users_count
            ]);
        }
    }

    private function handleAthlete($user) {

        $announcements = Announcement::where('is_deleted', false)->whereIn('role_id', $user->roles->pluck('id'))->where('type', '!=', '4')->orderBy('created_at', 'desc')->get();
        $direct_messages = Announcement::where([['is_deleted', false], ['type', '4'], ['user_id', $user->id]])->orderBy('created_at', 'desc')->get();
        $announcements = $announcements->merge($direct_messages);

        $seen_by_user = $user->seenAnnouncements()->get();

        $not_seen = [];

        foreach ($announcements as $announcement) {
            $found = false;

            foreach ($seen_by_user as $seen) {
                if ($seen->id == $announcement->id) {
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $not_seen[] = $announcement;
            }
        }


        $view = 'dashboard.athlete.index';
        return view($view, [
            'announcements' => $not_seen,

        ]);
    }

    public function languages(User $user, Request $request) {

        $role = $user->getRole();

        if ($role === 'athlete') {

            $authUserId = auth()->user()->id;

            if ($authUserId !== $user->id) {
                return response()->json([
                    'error' => 'You do not have the required role to access this page!',
                ]);
            }
        }


        $user->languages()->detach();

        $languages = explode(',', $request->languages);

        foreach ($languages as $language) {
            $user->languages()->syncWithoutDetaching($language);
        }

        return response()->json([
            'success' => true,
        ]);
    }

    public function invoicedata(User $user) {
        $authUser = User::find(auth()->user()->id);
        if ($authUser->getRole() == 'athlete' && ($authUser->id != $user->id)) {
            return response()->json([
                'error' => 'You do not have permission for this data!',
            ]);
        }

        $last_invoice = $user->invoices()->latest()->first();

        if ($last_invoice) {
            return response()->json($last_invoice);
        } else {
            return response()->json([
                'name' => $user->name,
                'surname' => $user->surname,
                'address' => [
                    'address' => '',
                    'zip' => '',
                    'city' => '',
                    'country' => $user->nation->name,
                ],
                'vat' => '',
                'sdi' => '',
                'is_business' => false,
                'business_name' => '',
            ]);
        }
    }

    public function saveInvoice(Request $request) {

        $address = json_encode([
            'address' => $request->address,
            'zip' => $request->zip,
            'city' => $request->city,
            'country' => $request->country,
        ]);

        $invoice = Invoice::create([
            'name' => $request->name,
            'surname' => $request->surname,
            'vat' => $request->vat ?? '',
            'sdi' => $request->sdi ?? '',
            'address' => $address,
            'is_business' => $request->is_business === 'true' ? true : false,
            'want_invoice' => $request->want_invoice === 'true' ? true : false,
            'business_name' => $request->business_name ?? '',
            'user_id' => Auth()->user()->id
        ]);

        $invoice->save();

        return response()->json([
            'success' => true,
        ]);
    }

    public function updateInvoice(Request $request) {

        if ($request->want_invoice == 'true') {
            if ($request->is_business == 'true' && (!$request->vat || !$request->business_name)) {
                return response()->json([
                    'error' => 'Business invoice requires VAT and Business Name!',
                ]);
            }
            if (((preg_match('/^IT/', $request->vat) || strtolower($request->country) === 'italy' || strtolower($request->country) === 'italia'))
                && !$request->sdi
            ) {
                return response()->json([
                    'error' => 'For Italy SDI code is required!',
                ]);
            }
        }

        $invoice = Invoice::find($request->invoice_id);

        if (!$invoice) {
            return response()->json([
                'error' => 'Invoice not found',
            ]);
        }
        if (
            User::find(Auth()->user()->id)->getRole() != 'admin'
            && (Auth()->user()->id != Invoice::find($request->invoice_id)->user_id)
        ) {
            return response()->json([
                'error' => 'You do not have permission for this data!',
            ]);
        }

        $address = json_encode([
            'address' => $request->address,
            'zip' => $request->zip,
            'city' => $request->city,
            'country' => $request->country,
        ]);

        $invoice = Invoice::find($request->invoice_id);

        $invoice->name = $request->name;
        $invoice->surname = $request->surname;
        $invoice->vat = $request->vat ? $request->vat : 'VAT';
        $invoice->sdi = $request->sdi;
        $invoice->address = $address;
        $invoice->is_business = $request->is_business === 'true' ? true : false;
        $invoice->want_invoice = $request->want_invoice === 'true' ? true : false;
        $invoice->business_name = $request->business_name;

        $invoice->save();

        return response()->json([
            'success' => true,
        ]);
    }

    public function testUserTest() {
        return response()->json("hi there");
    }

    public function setMainInstitution(Request $request) {

        $validator = Validator::make($request->all(), [
            'institution_type' => 'required|string|in:academy,school',
            'role_type' => 'required|string|in:personnel,athlete',
            'user_id' => 'required|integer',
            'academy_id' => 'integer',
            'school_id' => 'integer',
        ]);

        if ($validator->fails()) {
            return back()->with('error', $validator->errors()->first());
        }

        $user = User::find($request->user_id);

        if ($request->institution_type == 'academy') {
            $academy = Academy::find($request->academy_id);
            if (!$academy) {
                return back()->with('error', 'Academy not found!');
            }
            if ($request->role_type == 'personnel') {
                // Logica per modificare l'ordine delle accademie - personale
                $user->setPrimaryAcademy($academy->id);

                if ($academy->id != 1 && ($user->academies()->count() > 1) && ($user->academies->where('id', 1)->count() > 0)) {
                    $user->academies()->detach(1);
                }
            } else {
                // Logica per modificare l'ordine delle accademie - atleti
                $user->setPrimaryAcademyAthlete($academy->id);

                if ($academy->id != 1 && ($user->academyAthletes()->count() > 1) && ($user->academyAthletes->where('id', 1)->count() > 0)) {
                    $user->academyAthletes()->detach(1);
                }
            }
        }

        if ($request->institution_type == 'school') {
            $school = School::find($request->school_id);
            if (!$school) {
                return back()->with('error', 'School not found!');
            }
            if ($request->role_type == 'personnel') {
                // Logica per modificare l'ordine delle scuole - personale
                $user->setPrimarySchool($school->id);
            } else {
                // Logica per modificare l'ordine delle scuole - atleti
                $user->setPrimarySchoolAthlete($school->id);
            }
        }

        return back()->with('success', 'Main ' . $request->institution_type . ' as ' . $request->role_type . ' set successfully!');
    }

    public function roleSelector() {
        $user = auth()->user();
        $user = User::find($user->id);
        $roles = $user->roles()->get();

        return view('role-selector', [
            'roles' => $roles
        ]);
    }

    public function athletesDataForWorld() {
        $athletes = User::where('is_disabled', false)->whereHas('roles', function ($q) {
            $q->where('label', 'athlete');
        })->get();
        $active_users = 0;
        $active_users_no_course = 0;
        $users_course_not_active = 0;
        $new_users_this_year = 0;

        foreach ($athletes as $key => $athlete) {

            if ($athlete->has_paid_fee) {
                $active_users++;
            }

            if (($athlete->has_paid_fee) && ($athlete->clans()->count() == 0)) {
                $active_users_no_course++;
            }

            if ((!$athlete->has_paid_fee) && ($athlete->clans()->count() > 0)) {
                $users_course_not_active++;
            }

            if ($athlete->created_at->year == now()->year) {
                $new_users_this_year++;
            }
        }

        return response()->json([
            'active_users' => $active_users,
            'active_users_no_course' => $active_users_no_course,
            'users_course_not_active' => $users_course_not_active,
            'new_users_this_year' => $new_users_this_year,
        ]);
    }

    public function athletesDataWorldList() {

        $filledNations = Nation::whereHas('academies')->get();

        $nations = [];

        foreach ($filledNations as $nation) {

            $validAcademies = $nation->academies->where('is_disabled', false);

            $academies = [];
            $nationUniqueAthletes = collect();

            foreach ($validAcademies as $academy) {

                $athletes = $academy->athletes->where('is_disabled', false);
                $nationUniqueAthletes = $nationUniqueAthletes->merge($athletes);

                $schools = [];

                foreach ($academy->schools->where('is_disabled', false) as $key => $school) {

                    $courses = [];

                    foreach ($school->clan->where('is_disabled', false) as $course) {
                        $courses[] = [
                            'id' => $course->id,
                            'name' => $course->name,
                            'athletes' => $course->users->where('is_disabled', false)->count(),
                        ];
                    }

                    usort($courses, function ($a, $b) {
                        return $b['athletes'] - $a['athletes'];
                    });

                    $schools[] = [
                        'id' => $school->id,
                        'name' => $school->name,
                        'athletes' => $school->athletes->where('is_disabled', false)->count(),
                        'courses' => $courses,
                    ];
                }

                usort($schools, function ($a, $b) {
                    return $b['athletes'] - $a['athletes'];
                });

                $academies[] = [
                    'id' => $academy->id,
                    'name' => $academy->name,
                    'athletes' => $athletes->count(),
                    'schools' => $schools,
                ];
            }

            $nationUniqueAthletes = $nationUniqueAthletes->unique('id')->count();

            usort($academies, function ($a, $b) {
                return $b['athletes'] - $a['athletes'];
            });

            $nations[] = [
                'id' => $nation->id,
                'name' => $nation->name,
                'athletes' => $nationUniqueAthletes,
                'academies' => $academies,
            ];
        }

        usort($nations, function ($a, $b) {
            return $b['athletes'] - $a['athletes'];
        });

        return response()->json($nations);
    }

    public function getWorldAthletesNumberPerYear() {
        $athletes = User::where('is_disabled', false)->whereHas('roles', function ($q) {
            $q->where('label', 'athlete');
        })->get();
        $athletes_last_year = 0;
        $athletes_this_year = 0;

        foreach ($athletes as $athlete) {
            $athlete->created_at->year == now()->year ? $athletes_this_year++ : $athletes_last_year++;
        }

        return response()->json([
            'last_year' => $athletes_last_year,
            'this_year' => $athletes_this_year,
        ]);
    }
}
