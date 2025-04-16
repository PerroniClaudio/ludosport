<?php

namespace App\Http\Controllers;

use App\Mail\CreatedUserEmail;
use App\Models\Academy;
use App\Models\Announcement;
use App\Models\Clan;
use App\Models\Fee;
use App\Models\Invoice;
use App\Models\Language;
use App\Models\Nation;
use App\Models\Order;
use App\Models\Rank;
use App\Models\Role;
use App\Models\School;
use App\Models\User;
use App\Models\WeaponForm;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Password;

class UserController extends Controller {

    public function index() {
        $authUser = User::find(Auth::user()->id);
        $authUserRole = $authUser->getRole();

        if (!in_array($authUserRole, ['admin', 'rector', 'dean', 'manager', 'technician', 'instructor'])) {
            return redirect()->route("dashboard")->with('error', 'You do not have the required role to access this page!');
        }

        $roles = Role::all();
        $users_sorted_by_role = [];
        $users = [];
        $users_without_roles = [];

        switch ($authUserRole) {
            case 'admin':
            case 'technician':

                // Tutti gli utenti

                $users = User::all()->where('is_disabled', false);

                // put all users without roles in a no-role array inside the users_sorted_by_role array
                $no_roles_users = $users->filter(function ($user) {
                    return $user->roles->isEmpty();
                });
                $users_without_roles = [];
                foreach ($no_roles_users as $user) {
                    $users_without_roles[] = $user;
                }


                break;
            case 'rector':

                // Utenti di una determinata accademia

                $academy_id = $authUser->primaryAcademy()->id ?? null;
                if (!$academy_id) {
                    return redirect()->route("dashboard")->with('error', 'You don\'t have an academy assigned!');
                }

                $users = User::whereHas('academies', function (Builder $query) use ($academy_id) {
                    $query->where('academy_id', $academy_id);
                })->orWhereHas('academyAthletes', function (Builder $query) use ($academy_id) {
                    $query->where('academy_id', $academy_id);
                })->where('is_disabled', false)->get();

                break;
            case 'instructor':
                // Utenti di tutte le accademie in cui ha un corso (tanto non può entrare nel dettaglio utente)
                // $academy_id = $authUser->primaryAcademy()->id ?? null;
                // if (!$academy_id) {
                //     return redirect()->route("dashboard")->with('error', 'You don\'t have an academy assigned!');
                // }

                $academiesIds = $authUser->academies()->pluck('academy_id')->toArray();
                $users = User::whereHas('academies', function (Builder $query) use ($academiesIds) {
                    $query->whereIn('academy_id', $academiesIds);
                })->orWhereHas('academyAthletes', function (Builder $query) use ($academiesIds) {
                    $query->whereIn('academy_id', $academiesIds);
                })->where('is_disabled', false)->get();

                break;
            case 'dean':
            case 'manager':
                // Utenti di una determinata scuola

                $school_id = $authUser->primarySchool()->id ?? null;

                if (!$school_id) {
                    return redirect()->route("dashboard")->with('error', 'You don\'t have a school assigned!');
                }

                $users = User::whereHas('schools', function (Builder $query) use ($school_id) {
                    $query->where('school_id', $school_id);
                })->orWhereHas('schoolAthletes', function (Builder $query) use ($school_id) {
                    $query->where('school_id', $school_id);
                })->where('is_disabled', false)->get();


                break;
            default:
                return redirect()->route("dashboard")->with('error', 'You are not authorized to access this page!');
                break;
        }

        $viewPath = $authUserRole === 'admin' ? 'users.index' : 'users.' . $authUserRole . '.index';



        foreach ($roles as $role) {
            $filtered = $users->filter(function ($user) use ($role) {
                return $user->hasRole($role->label);
            });

            if ($filtered->isEmpty()) {
                $users_sorted_by_role[$role->label] = [];
            } else {
                foreach ($filtered as $user) {

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

                    if ($role->label === 'instructor') {
                        $user->weapon_forms_instructor_formatted = $user->weaponFormsPersonnel()->pluck('name')->toArray();
                    }

                    if ($role->label === 'technician') {
                        $user->weapon_forms_technician_formatted = $user->weaponFormsTechnician()->pluck('name')->toArray();
                    }

                    if ($role->label === 'rector') {
                        $user->primary_academy = $user->primaryAcademy() ? $user->primaryAcademy()->name : "No academy";
                    }

                    if ($role->label === 'dean') {
                        $user->primary_school = $user->primarySchool() ? $user->primarySchool()->name : "No school";
                    }

                    $users_sorted_by_role[$role->label][] = $user;
                }
            }
        }

        return view($viewPath, [
            'users' => $users_sorted_by_role,
            'users_without_roles' => $users_without_roles ?? [],
            'roles' => $roles,
        ]);
    }

    public function create() {
        $authUser = User::find(Auth::user()->id);
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
        $authUser = User::find(Auth::user()->id);
        $authRole = $authUser->getRole();

        if (!in_array($authRole, ['admin', 'rector', 'dean', 'manager'])) {
            return back()->with('error', 'You do not have the required role to access this page!');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
        ]);

        $roles = explode(',', $request->roles);
        if (count($roles) < 1 || ($roles[0] === '')) {
            return back()->with('error', 'You must assign at least one role to the user!');
        }

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
            // Un atelta senza scuola è associato alla scuola No school
            $noSchool = School::where('slug', 'no-school')->first();
            // Se creato da dean o manager va associato alla scuola altrimenti non lo possono vedere.
            // Dato che dean e manager sono abilitati ad una scuola sola, basta recuperare quella.
            if (in_array($authRole, ['dean', 'manager']) && $authUser->primarySchool()) {
                $school = $authUser->primarySchool();
                $school->athletes()->syncWithoutDetaching($user->id);
                $user->setPrimarySchoolAthlete($school->id);
            } else if ($noSchool) {
                $user->schoolAthletes()->syncWithoutDetaching($noSchool->id);
            }
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
        $authUser = User::find(Auth::user()->id);
        $authRole = $authUser->getRole();

        if (!in_array($authRole, ['admin', 'rector', 'dean', 'manager'])) {
            return back()->with('error', 'You do not have the required role to access this page!');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'surname' => 'required|string|max:255',
        ]);

        // Controlla il ruolo prima di creare l'utente. se la richiesta non è di tipo atleta deve avere almeno un altro ruolo
        if ($request->type !== "athlete") {
            $roles = explode(',', $request->roles);
            if (count($roles) < 1 || ($roles[0] === '')) {
                return back()->with('error', 'You must assign at least one role to the user!');
            }
        }


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
            // Un atelta senza scuola è associato alla scuola No school
            $noSchool = School::where('slug', 'no-school')->first();
            if ($noSchool) {
                $user->schoolAthletes()->syncWithoutDetaching($noSchool->id);
            }
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
        $authUser = User::find(Auth::user()->id);
        $authRole = $authUser->getRole();

        if (!in_array($authRole, ['admin', 'rector', 'dean', 'manager'])) {
            return back()->with('error', 'You do not have the required role to access this page!');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'surname' => 'required|string|max:255',
        ]);

        // Controlla il ruolo prima di creare l'utente. se la richiesta non è di tipo atleta deve avere almeno un altro ruolo
        if ($request->type !== "athlete") {
            $roles = explode(',', $request->roles);
            if (count($roles) < 1 || ($roles[0] === '')) {
                return back()->with('error', 'You must assign at least one role to the user!');
            }
        }

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
        $authUser = User::find(Auth::user()->id);
        $authRole = $authUser->getRole();

        if (!in_array($authRole, ['admin', 'rector', 'dean', 'manager'])) {
            return back()->with('error', 'You do not have the required role to access this page!');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'surname' => 'required|string|max:255',
        ]);

        // Controlla il ruolo prima di creare l'utente. se la richiesta non è di tipo atleta deve avere almeno un altro ruolo
        if ($request->type !== "athlete") {
            $roles = explode(',', $request->roles);
            if (count($roles) < 1 || ($roles[0] === '')) {
                return back()->with('error', 'You must assign at least one role to the user!');
            }
        }

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
        $authUser = User::find(Auth::user()->id);
        $authRole = $authUser->getRole();
        if (!in_array($authRole, ['admin', 'rector', 'dean', 'manager'])) {
            return back()->with('error', 'You do not have the required role to access this page!');
        }

        // Possono vedere solo le persone associate alla loro accademia/scuola come atleti o come personale
        if (($authRole === "rector" && (!in_array($authUser->primaryAcademy()->id, $user->academyAthletes->pluck('id')->toArray()) && !in_array($authUser->primaryAcademy()->id, $user->academies->pluck('id')->toArray())))
            || (in_array($authRole, ['dean', 'manager']) && (!in_array($authUser->primarySchool()->id, $user->schoolAthletes()->pluck('school_id')->toArray()) && !in_array($authUser->primarySchool()->id, $user->schools()->pluck('school_id')->toArray())))
        ) {
            return back()->with('error', 'You are not authorized to access this page!');
        }

        $ranks = Rank::all()->pluck('name', 'id');

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
        $allWeaponForms = WeaponForm::all();

        // Per select-institutions
        $allAcademies = Academy::all();
        $authRole = User::find(Auth::user()->id)->getRole();
        $viewPath = $authRole === 'admin' ? 'users.edit' :  'users.' . $authRole . '.edit';
        $filteredSchoolsPersonnel = School::whereIn('academy_id', $user->academies->pluck('id'))->whereNotIn('id', $user->schools->pluck('id'))->where('is_disabled', '0')->with(['nation'])->get();
        $filteredSchoolsAthlete = School::whereIn('academy_id', $user->academyAthletes->pluck('id'))->whereNotIn('id', $user->schoolAthletes->pluck('id'))->where('is_disabled', '0')->with(['nation'])->get();

        return view($viewPath, [
            'user' => $user,
            'academies' => $user->nation->academies ?? [],
            'allAcademies' => $allAcademies,
            'schools' => $schools,
            'nations' => $countries,
            'roles' => $roles,
            'languages' => $allLanguages,
            'ranks' => $ranks,
            'allWeaponForms' => $allWeaponForms,
            'filteredSchoolsPersonnel' => $filteredSchoolsPersonnel,
            'filteredSchoolsAthlete' => $filteredSchoolsAthlete,
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
        $authUser = User::find(Auth::user()->id);
        $authRole = $authUser->getRole();

        if (!in_array($authRole, ['admin', 'rector', 'dean', 'manager'])) {
            return back()->with('error', 'You do not have the required role to access this page!');
        }

        $canUpdate = true;
        switch ($authRole) {
            case 'admin':
                break;
            case 'rector':
                if (!in_array(
                    $authUser->primaryAcademy()->id,
                    array_merge(
                        $user->academies()->pluck('academy_id')->toArray(),
                        [$user->primaryAcademyAthlete() ? $user->primaryAcademyAthlete()->id : null]
                    )
                )) {
                    $canUpdate = false;
                }
                break;
            case 'dean':
            case 'manager':
                if (!in_array(
                    $authUser->primarySchool()->id,
                    array_merge(
                        $user->schools()->pluck('school_id')->toArray(),
                        [$user->primarySchoolAthlete() ? $user->primarySchoolAthlete()->id : null]
                    )
                )) {
                    $canUpdate = false;
                }
                break;
            default:
                return back()->with('error', 'You do not have the required role to access this page!');
        }
        if (!$canUpdate) {
            return back()->with('error', 'You are not authorized to edit this user!');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'year' => ['required', 'int', 'min:' . 2006, 'max:' . (date('Y'))],
            'nationality' => 'required|string|exists:nations,id',
        ]);

        // Controlla il ruolo prima di creare l'utente. se la richiesta non è di tipo atleta deve avere almeno un altro ruolo
        $newRoles = explode(',', $request->roles);
        if (count($newRoles) < 1 || ($newRoles[0] === '')) {
            return back()->with('error', 'You must assign at least one role to the user!');
        }

        // Per il log dopo la modifica
        $oldHasPaidFee = $user->has_paid_fee;
        $newHasPaidFee = $user->has_paid_fee;
        $oldRank = $user->rank_id;
        $newRank = $user->rank_id;

        if ($authRole === 'admin') {
            $newRank = Rank::find($request->rank)->id ?? $user->rank_id;
            $newHasPaidFee = ($request->has_paid_fee == 'on' ?  1 :  0);

            if ($oldHasPaidFee == 0 && $newHasPaidFee == 1) {

                $lastInvoice = $user->invoices()->latest()->first();
                // L'invoice in questo caso non servirebbe, ma è richiesta dall'ordine, quindi si crea comunque.
                $invoice = $user->invoices()->create([
                    'user_id' => $user->id,
                    'name' => $lastInvoice ? ($lastInvoice->name ?: $user->name) : $user->name,
                    'surname' => $lastInvoice ? ($lastInvoice->surname ?: ($user->surname ?: '')) : ($user->surname ?: ''),
                    'address' => $lastInvoice ? ($lastInvoice->address ?: json_encode([
                        'address' => '',
                        'zip' => '',
                        'city' => '',
                        'country' => 'Italy',
                    ])) : json_encode([
                        'address' => '',
                        'zip' => '',
                        'city' => '',
                        'country' => 'Italy',
                    ]),
                    'vat' => $lastInvoice ? ($lastInvoice->vat ?: '') : '',
                    'sdi' => $lastInvoice ? ($lastInvoice->sdi ?: '') : '',
                ]);

                $order = Order::create([
                    'user_id' => $user->id,
                    'status' => 2, // Completed
                    'total' => 0,
                    'payment_method' => 'admin',
                    'order_number' => Str::orderedUuid(),
                    'result' => '{}',
                    'invoice_id' => $invoice->id,
                ]);

                $order->items()->create([
                    'product_type' => 'fee',
                    'product_name' => 'fee',
                    'product_code' => 'fee',
                    'quantity' => 1,
                    'price' => 0,
                    'vat' => 0,
                    'total' => 0
                ]);

                $fee = Fee::create([
                    'user_id' => $order->user_id,
                    'academy_id' => $user->primaryAcademyAthlete()->id ?? 1,
                    'type' => 3,
                    'start_date' => now(),
                    'end_date' => now()->addYear()->endOfYear()->format('Y') . '-08-31',
                    'auto_renew' => 0,
                    'unique_id' => Str::orderedUuid(),
                    'used' => 1,
                ]);
            }
        }

        $user->update([
            'name' => $request->name,
            'surname' => $request->surname,
            'email' => $request->email,
            'subscription_year' => $request->year,
            'nation_id' => $request->nationality,
            'has_paid_fee' => $newHasPaidFee,
            'rank_id' => $newRank,
        ]);

        if ($oldHasPaidFee != $newHasPaidFee) {
            Log::channel('fee')->info('Fee changed', [
                'user_id' => $user->id,
                'made_by' => $authUser->id,
                'old_value' => $oldHasPaidFee,
                'new_value' => $newHasPaidFee,
            ]);
        }

        if ($oldRank != $newRank) {
            Log::channel('rank')->info('Rank changed', [
                'user_id' => $user->id,
                'made_by' => $authUser->id,
                'old_value' => $oldRank,
                'new_value' => $newRank,
            ]);
        }

        // Recupera i ruoli attuali dell'utente
        $currentRoles = $user->roles->pluck('label')->toArray();
        // Recupera i nuovi ruoli dal request
        $newRoles = explode(',', $request->roles);

        // Recupera l'utente che ha fatto la richiesta
        $authUser = User::find(Auth::user()->id);

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

            // Se è stato aggiunto il ruolo da utente si deve assegnare l'accademia primaria da atleta
            // Se ce l'ha ok, altrimenti si mette come primaria la prima accademia a cui è associato (solo una per atleta), altrimenti si assegna no academy
            if (in_array('athlete', $rolesToAdd)) {
                if (!$user->primaryAcademyAthlete()) {
                    if ($user->academyAthletes()->first()) {
                        $academy = $user->academyAthletes()->first();
                        $user->setPrimaryAcademyAthlete($academy->id);
                    } else {
                        $user->academyAthletes()->syncWithoutDetaching(1);
                        $user->setPrimaryAcademyAthlete(1);
                    }
                }
            }

            // Se ha un ruolo da personale deve avere almeno un'accademia associata (anche se non primaria), altrimenti si associa a no academy
            if (array_intersect($rolesToAdd, ['rector', 'dean', 'manager', 'instructor', 'technician'])) {
                if (!$user->primaryAcademy()) {
                    if (!!$user->academies()->first()) {
                        $user->setPrimaryAcademy($user->academies()->first()->id);
                    } else if (!!$user->primaryAcademyAthlete()) {
                        $user->academies()->syncWithoutDetaching($user->primaryAcademyAthlete()->id);
                        $user->setPrimaryAcademy($user->primaryAcademyAthlete()->id);
                    } else {
                        $user->academies()->syncWithoutDetaching(1);
                        $user->setPrimaryAcademy(1);
                    }
                }
                // Nel caso di dean, manager e instructor si assegna anche la scuola in automatico
                if (array_intersect($rolesToAdd, ['dean', 'manager', 'instructor'])) {
                    if (!$user->primarySchool()) {
                        if (!!$user->schools()->first()) {
                            $user->setPrimarySchool($user->schools()->first()->id);
                        } else if (!!$user->primarySchoolAthlete()) {
                            $user->schools()->syncWithoutDetaching($user->primarySchoolAthlete()->id);
                            $user->setPrimarySchool($user->primarySchoolAthlete()->id);
                        }
                    }
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

            // Se è stato rimosso il ruolo da atleta si deve rimuovere l'accademia da atleta (anche primaria), così come tutti i collegamenti con corsi e scuole
            if (in_array('athlete', $rolesToRemove)) {
                $removedCourses = $user->clans()->get();
                $removedSchools = $user->schoolAthletes()->get();
                $removedAcademies = $user->academyAthletes()->get();

                $user->clans()->detach(); // I clan da atleta si prendono con clans e gli altri con clansPersonnel
                $user->schoolAthletes()->detach();
                $user->academyAthletes()->detach();

                Log::channel('user')->info('Removed athlete associations', [
                    'made_by' => $authUser->id,
                    'athlete' => $user->id,
                    'academies' => $removedAcademies->pluck('id')->toArray(),
                    'schools' => $removedSchools->pluck('id')->toArray(),
                    'courses' => $removedCourses->pluck('id')->toArray(),
                ]);
            }

            // Se è stato rimosso il ruolo da personale e non ha più nessun altro ruolo tale, si deve rimuovere l'accademia da personale (anche primaria), così come tutti i collegamenti con corsi e scuole
            if (array_intersect($rolesToRemove, ['rector', 'dean', 'manager', 'instructor', 'technician'])) {
                if (!array_intersect($newRoles, ['rector', 'dean', 'manager', 'instructor', 'technician'])) {
                    $removedCourses = $user->clansPersonnel()->get();
                    $removedSchools = $user->schools()->get();
                    $removedAcademies = $user->academies()->get();

                    $user->clansPersonnel()->detach();
                    $user->schools()->detach();
                    $user->academies()->detach();

                    Log::channel('user')->info('Removed personnel associations', [
                        'made_by' => $authUser->id,
                        'personnel' => $user->id,
                        'academies' => $removedAcademies->pluck('id')->toArray(),
                        'schools' => $removedSchools->pluck('id')->toArray(),
                        'courses' => $removedCourses->pluck('id')->toArray(),
                    ]);
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


        $authUserRole = User::find(Auth::user()->id)->getRole();
        $redirectRoute = $authUserRole === 'admin' ? 'users.edit' :  $authUserRole . '.users.edit';

        return redirect()->route($redirectRoute, $user->id)->with('success', 'User updated successfully!');
    }

    public function destroy(User $user) {
        $authRole = User::find(Auth::user()->id)->getRole();

        // if (!in_array($authRole, ['admin', 'rector', 'dean', 'manager'])) {
        if (!in_array($authRole, ['admin'])) {
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

        $authUser = User::find(Auth::user()->id);
        $authRole = $authUser->getRole();

        switch ($authRole) {
            case 'admin':
            case 'technician':
                $users = User::query()
                    ->when($request->search, function (Builder $q, $value) {
                        /** 
                         * @disregard Intelephense non rileva il metodo whereIn
                         */
                        return $q->whereIn('id', User::search($value)->keys());
                    })->with(['roles', 'academies', 'academyAthletes', 'nation'])->get();
                break;
            case 'rector':
                if (!$authUser->primaryAcademy()) {
                    return back()->with('error', 'You are not authorized to access this page!');
                }
                $users = User::query()
                    ->when($request->search, function (Builder $q, $value) {
                        /** 
                         * @disregard Intelephense non rileva il metodo whereIn
                         */
                        return $q->whereIn('id', User::search($value)->keys());
                    })->where(function ($query) use ($authUser) {
                        $query->whereHas('academies', function ($q) use ($authUser) {
                            return $q->where('academies.id', $authUser->primaryAcademy()->id);
                        })->orWhereHas('academyAthletes', function ($q) use ($authUser) {
                            return $q->where('academies.id', $authUser->primaryAcademy()->id);
                        });
                    })->with(['roles', 'academies', 'academyAthletes', 'nation'])->get();
                break;
            case 'dean':
            case 'manager':
                if (!$authUser->primarySchool()) {
                    return back()->with('error', 'You are not authorized to access this page!');
                }
                $users = User::query()
                    ->when($request->search, function (Builder $q, $value) {
                        /** 
                         * @disregard Intelephense non rileva il metodo whereIn
                         */
                        return $q->whereIn('id', User::search($value)->keys());
                    })->where(function ($query) use ($authUser) {
                        $query->whereHas('schools', function ($q) use ($authUser) {
                            return $q->where('schools.id', $authUser->primarySchool()->id);
                        })->orWhereHas('schoolAthletes', function ($q) use ($authUser) {
                            return $q->where('schools.id', $authUser->primarySchool()->id);
                        });
                    })->with(['roles', 'academies', 'academyAthletes', 'nation'])->get();
                break;
            case 'instructor':
                if (!$authUser->primarySchool()) {
                    return back()->with('error', 'You are not authorized to access this page!');
                }
                $schoolsIds = $authUser->clansPersonnel->map(function ($clan) {
                    return $clan->school->id;
                })->toArray();
                $users = User::query()
                    ->when($request->search, function (Builder $q, $value) {
                        /** 
                         * @disregard Intelephense non rileva il metodo whereIn
                         */
                        return $q->whereIn('id', User::search($value)->keys());
                    })->where(function ($query) use ($schoolsIds) {
                        $query->whereHas('schools', function ($q) use ($schoolsIds) {
                            return $q->whereIn('schools.id', $schoolsIds);
                        })->orWhereHas('schoolAthletes', function ($q) use ($schoolsIds) {
                            return $q->whereIn('schools.id', $schoolsIds);
                        });
                    })->with(['roles', 'academies', 'academyAthletes', 'nation'])->get();
                break;
            default:
                return back()->with('error', 'You are not authorized to access this page!');
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
        $authUser = User::find(Auth::user()->id);
        $authRole = $authUser->getRole();

        switch ($authRole) {
            case 'admin':
            case 'technician':
                // case 'rector':
                // case 'dean':
                // case 'manager':
                $academies = Academy::where('is_disabled', false)->with('nation')->get();
                break;
            case 'rector':
                $academies = collect([$authUser->primaryAcademy()]);
                break;
            case 'dean':
            case 'manager':
                $academies = collect(($authUser->primarySchool()->academy ?? null) ? [$authUser->primarySchool()->academy] : []);
                break;
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
        $authUser = User::find(Auth::user()->id);
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

        // Serve solo per dean e manager. scuola in cui lavora.
        $authPrimarySchool = $authUser->primarySchool();

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

            // Il dean e il manager possono vedere solo gli utenti della loro scuola.
            if (in_array($authUserRole, ['dean', 'manager'])) {
                if (
                    $user->schools->where('id', $authPrimarySchool->id)->isEmpty()
                    && $user->schoolAthletes->where('id', $authPrimarySchool->id)->isEmpty()
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

        $authUser = User::find(Auth::user()->id);

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

            // Validate the uploaded image
            $request->validate([
                'profilepicture' => 'image|max:8192', // 8MB max
            ]);

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

            // Validate the uploaded image
            $request->validate([
                'profilepicture' => 'image|max:8192', // 8MB max
            ]);

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
                return redirect()->route('profile.edit', $id)->with('error', 'Error uploading profile picture!');
            }
        } else {
            return redirect()->route('profile.edit', $id)->with('error', 'Error uploading profile picture!');
        }
    }

    public function dashboard(Request $request) {
        $user = Auth::user()->id;
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

        $seen_announcements = $user->seenAnnouncements()->get();
        $announcements = Announcement::where('is_deleted', false)->where('type', '!=', '4')->orderBy('created_at', 'desc')->get();
        $direct_messages = Announcement::where([['is_deleted', false], ['type', '4'], ['user_id', $user->id]])->orderBy('created_at', 'desc')->get();
        $announcements = $announcements->merge($direct_messages);

        // Verifica se sei della nazione/accademia/ruolo giusti per visualizzare l'annuncio

        $announcements = $announcements->filter(function ($announcement) use ($user) {

            $nations = $announcement->nations != null ? json_decode($announcement->nations) : null;
            $academies = $announcement->academies != null ? $academies = json_decode($announcement->academies) : null;
            $allowed_roles = $announcement->roles != null ? json_decode($announcement->roles) : null;

            if ($nations != null) {
                if (!in_array($user->nation_id, $nations)) {
                    return false;
                }
            }

            if ($academies != null) {

                if ($allowed_roles == null) {
                    $allAcademies = $user->academies->pluck('id')->merge($user->primaryAcademyAthlete() ? [$user->primaryAcademyAthlete()->id] : []);
                    if (!array_intersect($allAcademies->toArray(), $academies)) {
                        return false;
                    }
                } else {
                    $athleteRoleId = Role::where('name', 'athlete')->first()->id;
                    $canSee = false;
                    if (in_array($athleteRoleId, $allowed_roles)) {
                        $primaryAcademyAthlete = $user->primaryAcademyAthlete() ? $user->primaryAcademyAthlete()->id : null;
                        if (in_array($primaryAcademyAthlete, $academies)) {
                            $canSee = true;
                        }
                    }
                    if (array_intersect($user->roles->where('id', '!=', $athleteRoleId)->pluck('id')->toArray(), $allowed_roles)) {
                        // $allAcademiesPersonnel = $user->academies->pluck('id')->toArray();
                        $primaryAcademyPersonnel = $user->primaryAcademy() ? $user->primaryAcademy()->id : null;
                        if (in_array($primaryAcademyPersonnel, $academies)) {
                            $canSee = true;
                        }
                    }

                    if (!$canSee) {
                        return false;
                    }
                }
            }

            if ($allowed_roles != null) {

                /** 
                 * 09/12/2024 - cambio funzione, adesso vede solo gli annunci per il ruolo scelto nella sessione attiva. 
                 * 16/12/2024 - modifica revertata
                 */

                if (!array_intersect($user->roles->pluck('id')->toArray(), $allowed_roles)) {
                    return false;
                }
            }

            return true;

        });

        $not_seen = [];

        foreach ($announcements as $announcement) {
            $found = false;

            foreach ($seen_announcements as $seen) {
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

        $loggedUser = User::find(Auth::user()->id);

        if ($loggedUser->getRole() == 'athlete' && $loggedUser->id != $user->id) {
            return response()->json([
                'error' => 'You do not have permission for this data!',
            ]);
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
        $authUser = User::find(Auth::user()->id);
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

    public function editWeaponFormsAthlete(Request $request, User $user) {
        $authUser = User::find(Auth::user()->id);
        $authUserRole = $authUser->getRole();

        if ($authUserRole !== 'admin') {
            return response()->json([
                'error' => 'You are not authorized to edit user\'s weapon forms!',
            ]);
        }

        $previousForms = $user->weaponForms;
        $requestForms = explode(',', $request->weapon_forms);

        $toRemove = $previousForms->whereNotIn('id', $requestForms);
        foreach ($toRemove as $form) {
            Log::channel('weapon_form')->info('Athlete weapon form removed', [
                'user_id' => $user->id,
                'form_id' => $form->id,
                'made_by' => $authUser->id,
            ]);
            $form->users()->detach($user->id);
        }


        $toAdd = $previousForms ? collect($requestForms)->diff($previousForms->pluck('id')) : $requestForms;
        foreach ($toAdd as $formId) {
            $form = WeaponForm::find($formId);
            if ($form) {
                Log::channel('weapon_form')->info('Athlete weapon form added', [
                    'user_id' => $user->id,
                    'form_id' => $form->id,
                    'made_by' => $authUser->id,
                ]);
                $form->users()->syncWithoutDetaching($user->id);
            }
        }

        return response()->json([
            'success' => true,
        ]);
    }

    public function editWeaponFormsPersonnel(Request $request, User $user) {
        $authUser = User::find(Auth::user()->id);
        $authUserRole = $authUser->getRole();

        if ($authUserRole !== 'admin') {
            return response()->json([
                'error' => 'You are not authorized to edit user\'s weapon forms!',
            ]);
        }

        $previousForms = $user->weaponFormsPersonnel;
        $requestForms = explode(',', $request->weapon_forms);

        $toRemove = $previousForms->whereNotIn('id', $requestForms);
        foreach ($toRemove as $form) {
            Log::channel('weapon_form')->info('Personnel weapon form removed', [
                'user_id' => $user->id,
                'form_id' => $form->id,
                'made_by' => $authUser->id,
            ]);
            $form->personnel()->detach($user->id);
        }


        $toAdd = $previousForms ? collect($requestForms)->diff($previousForms->pluck('id')) : $requestForms;
        foreach ($toAdd as $formId) {
            $form = WeaponForm::find($formId);
            if ($form) {
                Log::channel('weapon_form')->info('Personnel weapon form added', [
                    'user_id' => $user->id,
                    'form_id' => $form->id,
                    'made_by' => $authUser->id,
                ]);
                $form->personnel()->syncWithoutDetaching($user->id);
            }
        }

        return response()->json([
            'success' => true,
        ]);
    }

    public function editWeaponFormsTechnician(Request $request, User $user) {
        $authUser = User::find(Auth::user()->id);
        $authUserRole = $authUser->getRole();
        if ($authUserRole !== 'admin') {
            return response()->json([
                'error' => 'You are not authorized to edit user\'s weapon forms!',
            ]);
        }
        $previousForms = $user->weaponFormsTechnician;
        $requestForms = explode(',', $request->weapon_forms);

        $toRemove = $previousForms->whereNotIn('id', $requestForms);
        foreach ($toRemove as $form) {
            Log::channel('weapon_form')->info('Technician weapon form removed', [
                'user_id' => $user->id,
                'form_id' => $form->id,
                'made_by' => $authUser->id,
            ]);
            $form->technicians()->detach($user->id);
        }

        $toAdd = $previousForms ? collect($requestForms)->diff($previousForms->pluck('id')) : $requestForms;
        foreach ($toAdd as $formId) {
            $form = WeaponForm::find($formId);
            if ($form) {
                Log::channel('weapon_form')->info('Technician weapon form added', [
                    'user_id' => $user->id,
                    'form_id' => $form->id,
                    'made_by' => $authUser->id,
                ]);
                $form->technicians()->syncWithoutDetaching([['user_id' => $user->id, 'admin_id' => $authUser->id]]);
            }
        }

        return response()->json([
            'success' => true,
        ]);
    }

    public function editWeaponFormsAwardingDate(User $user, Request $request) {
        $authUser = User::find(Auth::user()->id);
        $authUserRole = $authUser->getRole();
        if ($authUserRole !== 'admin') {
            return response()->json([
                'error' => 'You are not authorized to edit user\'s weapon forms!',
            ]);
        }
        $request->validate([
            'form_id' => 'required|integer|exists:weapon_forms,id',
            'awarded_at' => 'required|date',
            'type' => 'required|string|in:athlete,personnel,technician',
        ]);

        switch ($request->type) {
            case 'athlete':
                $user->weaponForms()->updateExistingPivot($request->form_id, ['awarded_at' =>  \Carbon\Carbon::parse($request->awarded_at)]);
                break;
            case 'personnel':
                $user->weaponFormsPersonnel()->updateExistingPivot($request->form_id, ['awarded_at' => \Carbon\Carbon::parse($request->awarded_at)]);
                break;
            case 'technician':
                $user->weaponFormsTechnician()->updateExistingPivot($request->form_id, ['awarded_at' => \Carbon\Carbon::parse($request->awarded_at)]);
                break;
        }

        return back()->with('success', 'Awarding date updated successfully!');
        // return response()->json([
        //     'success' => true,
        // ]);
    }

    public function resetPassword(User $user) {

        $status = Password::sendResetLink(
            ['email' => $user->email]
        );


        return $status == Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withInput($user->email)
            ->withErrors(['email' => __($status)]);
    }

    public function associateAcademy(Request $request) {
        $authUser = User::find(Auth::user()->id);
        $authUserRole = $authUser->getRole();

        if ($authUserRole !== 'admin') {
            return response()->json([
                'error' => 'You are not authorized to associate user with academy!',
            ]);
        }

        $user = User::find($request->user_id);
        $academy = Academy::find($request->academy_id);

        if (!$academy) {
            return response()->json([
                'error' => 'Academy not found!',
            ]);
        }

        if ($request->type == 'personnel') {
            $user->academies()->syncWithoutDetaching($academy->id);
            Log::channel('academy')->info('Personnel associated with academy', [
                'user_id' => $user->id,
                'academy_id' => $academy->id,
                'made_by' => $authUser->id,
            ]);
            if ($user->academies->where('id', 1)->count() > 0) {
                // Se è associato a no academy viene rimosso
                $user->academies()->detach(1);
                $user->setPrimaryAcademy($academy->id);
            }
            // Se ha una sola associazione ad accademie, la rende primaria
            if ($user->academies->count() == 1) {
                $user->setPrimaryAcademy($academy->id);
            }
        } else if ($request->type == 'athlete') {
            if ($user->academyAthletes->contains($academy->id)) {
                return response()->json([
                    'error' => 'User is already associated with this academy!',
                ]);
            }
            if ($user->academyAthletes->count() > 0) {
                // L'atleta può essere associato ad una sola accademia.
                // Toglie l'associazione da tutte le accademie e crea il log. L'argomento è l'accademia che fa eccezione (se serve)
                $user->removeAcademiesAthleteAssociations();
            }

            $user->academyAthletes()->syncWithoutDetaching($academy->id);
            $user->setPrimaryAcademyAthlete($academy->id);

            // Se l'atleta non ha scuole come atleta viene assegnato a No school
            $noSchool = School::where('slug', 'no-school')->first();
            if ($user->schoolAthletes()->count() == 0) {
                $user->schoolAthletes()->syncWithoutDetaching($noSchool ? $noSchool->id : 1);
            }

            // Fallback se l'associazione non è andata a buon fine
            if ($user->academyAthletes->count() == 0) {
                // Se non ha accademie come atleta viene assegnato a No academy
                $user->academyAthletes()->syncWithoutDetaching(1);
                $user->setPrimaryAcademyAthlete(1);
            }
            Log::channel('academy')->info('Athlete associated with academy', [
                'user_id' => $user->id,
                'academy_id' => $academy->id,
                'made_by' => $authUser->id,
            ]);
        } else {
            return response()->json([
                'error' => 'Invalid type!',
            ]);
        }

        return response()->json([
            'success' => true,
        ]);
    }

    public function associateSchool(Request $request) {
        $authUser = User::find(Auth::user()->id);
        $authUserRole = $authUser->getRole();

        $user = User::find($request->user_id);
        $school = School::find($request->school_id);

        if ($authUserRole !== 'admin' && !($authUserRole === "rector" && ($authUser->primaryAcademy()->id === $school->academy->id))) {
            return response()->json([
                'error' => 'You are not authorized to associate this user with this school!',
            ]);
        }

        if ($request->type == 'personnel') {
            $user->schools()->syncWithoutDetaching($school->id);
            Log::channel('school')->info('Personnel associated with school', [
                'user_id' => $user->id,
                'school_id' => $school->id,
                'made_by' => $authUser->id,
            ]);
            // Se l'atleta non ha la scuola principale, la assegna
            if (!$user->primarySchool()) {
                $user->setPrimarySchool($school->id);
            }
        } else if ($request->type == 'athlete') {
            $user->schoolAthletes()->syncWithoutDetaching($school->id);
            Log::channel('school')->info('Athlete associated with school', [
                'user_id' => $user->id,
                'school_id' => $school->id,
                'made_by' => $authUser->id,
            ]);
            // Se l'atleta non ha la scuola principale, la assegna
            if (!$user->primarySchoolAthlete()) {
                $user->setPrimarySchoolAthlete($school->id);
            }
            // Se ha un'associazione con una scuola diversa da No school, rimuove quella con No school
            $noSchool = School::where('slug', 'no-school')->first();
            if ($noSchool && ($user->schoolAthletes()->whereNot('school_id', $noSchool->id)->count() > 0)) {
                $user->schoolAthletes()->detach($noSchool->id);
            }
        } else {
            return response()->json([
                'error' => 'Invalid type!',
            ]);
        }

        return response()->json([
            'success' => true,
        ]);
    }

    public function removeAcademy(Request $request) {
        $authUser = User::find(Auth::user()->id);
        $authUserRole = $authUser->getRole();

        if ($authUserRole !== 'admin') {
            return response()->json([
                'error' => 'You are not authorized to remove user from academy!',
            ]);
        }

        $user = User::find($request->user_id);
        $academy = Academy::find($request->academy_id);

        if (!$academy) {
            return response()->json([
                'error' => 'Academy not found!',
            ]);
        }

        if ($request->type == 'personnel') {
            // Prima di rimuovere l'associazione all'accademia rimuovere le associazioni a corsi e scuole
            // Rimuove tutte le associazioni a corsi, scuole e accademia indicata e crea i log
            $user->removeAcademyPersonnelAssociations($academy);

            if ($user->academies()->count() == 0) {
                // Se non ha accademie come personnel viene assegnato a No academy. Se si usa il codice più giù, si può rimuovere questo
                $user->academies()->syncWithoutDetaching(1);
                $user->setPrimaryAcademy(1);
                Log::channel('academy')->info('Personnel associated with academy', [
                    'user_id' => $user->id,
                    'academy_id' => 1,
                    'made_by' => $authUser->id,
                ]);
            }

            // Questa parte serve ad associare automaticamente una nuova accademia primaria. per ora la commentiamo perchè è preferibile associarle volutamente. Se il personale non ha l'istituzione primaria al massimo vede un messaggio di errore.
            // if($user->primaryAcademy() == null){
            //     $newAcademy = $user->academies->first();
            //     if($newAcademy){
            //         $user->setPrimaryAcademy($newAcademy->id);
            //         Log::channel('academy')->info('Personnel associated with academy', [
            //             'user_id' => $user->id,
            //             'academy_id' => 1,
            //             'made_by' => $authUser->id,
            //         ]);
            //     } else if ($user->academies()->count() == 0){
            //         // Se non ha accademie come personnel viene assegnato a No academy
            //         $user->academies()->syncWithoutDetaching(1);
            //         $user->setPrimaryAcademy(1);
            //         Log::channel('academy')->info('Personnel associated with academy', [
            //             'user_id' => $user->id,
            //             'academy_id' => 1,
            //             'made_by' => $authUser->id,
            //         ]);
            //     }
            // }
        } else if ($request->type == 'athlete') {
            // Dato che un atleta può essere associato ad una sola accademia possiamo usare la funzione che toglie l'associazione da tutte le accademie e crea il log
            // L'argomento è l'accademia che fa eccezione (se serve)
            $user->removeAcademiesAthleteAssociations();

            // Nel caso dell'atleta non si fa nessun danno ad associarlo in automatico se non ha l'istituzione primaria
            if ($user->primaryAcademyAthlete() == null) {
                $newAcademy = $user->academyAthletes->first();
                if ($newAcademy) {
                    $user->setPrimaryAcademyAthlete($newAcademy->id);
                    Log::channel('academy')->info('Athlete associated with academy', [
                        'user_id' => $user->id,
                        'academy_id' => 1,
                        'made_by' => $authUser->id,
                    ]);
                } else if ($user->academyAthletes->count() == 0) {
                    // Se non ha accademie come atleta viene assegnato a No academy
                    $user->academyAthletes()->syncWithoutDetaching(1);
                    $user->setPrimaryAcademyAthlete(1);
                    Log::channel('academy')->info('Athlete associated with academy', [
                        'user_id' => $user->id,
                        'academy_id' => 1,
                        'made_by' => $authUser->id,
                    ]);
                }
            }
            // Se ha un'associazione con una scuola diversa da No school, rimuove quella con No school, altimenti la aggiunge
            $noSchool = School::where('slug', 'no-school')->first();
            if ($noSchool) {
                if ($user->schoolAthletes()->whereNot('school_id', $noSchool->id)->count() > 0) {
                    $user->schoolAthletes()->detach($noSchool->id);
                } else {
                    $user->schoolAthletes()->syncWithoutDetaching($noSchool->id);
                }
            }
        } else {
            return response()->json([
                'error' => 'Invalid type!',
            ]);
        }

        return response()->json([
            'success' => true,
        ]);
    }

    public function removeSchool(Request $request) {
        $authUser = User::find(Auth::user()->id);
        $authUserRole = $authUser->getRole();

        $user = User::find($request->user_id);
        $school = School::find($request->school_id);

        if ($authUserRole !== 'admin' && !($authUserRole === "rector" && ($authUser->primaryAcademy()->id === $school->academy->id))) {
            return response()->json([
                'error' => 'You are not authorized to remove this user from this school!',
            ]);
        }

        if (!$school) {
            return response()->json([
                'error' => 'School not found!',
            ]);
        }

        if ($request->type == 'personnel') {
            // Rimuove tutte le associazioni (personnel) a corsi e scuola indicata e crea i log
            $user->removeSchoolPersonnelAssociations($school);

            // Questa parte serve ad associare automaticamente una nuova accademia primaria. per ora la commentiamo perchè è preferibile associarle volutamente. Se il personale non ha l'istituzione primaria al massimo vede un messaggio di errore.
            // if($user->primarySchool() == null){
            //     $newSchool = $user->schools->first();
            //     if($newSchool){
            //         $user->setPrimarySchool($newSchool->id);
            //     }
            // }
        } else if ($request->type == 'athlete') {
            // Rimuove tutte le associazioni (athlete) a corsi e scuola indicata e crea i log
            $user->removeSchoolAthleteAssociations($school);

            // Nel caso dell'atleta non si fa nessun danno ad associarlo in automatico se non ha l'istituzione primaria
            if ($user->primarySchoolAthlete() == null) {
                $noSchool = School::where('slug', 'no-school')->first();
                if ($noSchool) {
                    if ($user->schoolAthletes()->whereNot('school_id', $noSchool->id)->count() > 0) {
                        $user->schoolAthletes()->detach($noSchool->id);
                    } else {
                        $user->schoolAthletes()->syncWithoutDetaching($noSchool->id);
                    }
                }
                $newSchool = $user->schoolAthletes->first();
                if ($newSchool && $newSchool->id != $noSchool->id) {
                    $user->setPrimarySchoolAthlete($newSchool->id);
                }
            }
        } else {
            return response()->json([
                'error' => 'Invalid type!',
            ]);
        }

        return response()->json([
            'success' => true,
        ]);
    }
}
