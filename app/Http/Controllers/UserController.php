<?php

namespace App\Http\Controllers;

use App\Models\Academy;
use App\Models\Announcement;
use App\Models\Clan;
use App\Models\Nation;
use App\Models\Role;
use App\Models\School;
use App\Models\User;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserController extends Controller {

    public function index() {
        $roles = Role::all();
        $users_sorted_by_role = [];
        foreach ($roles as $role) {

            $users = [];

            foreach ($role->users as $user) {
                if ($user->is_disabled) {
                    continue;
                }

                if ($role->label === 'athlete') {
                    $user->academy = $user->academyAthletes->first();
                    $user->school = $user->schoolAthletes->first();
                    if ($user->academy) {
                        $user->nation = $user->academy->nation->name;
                    } else {
                        $nation = Nation::find($user->nation_id);
                        $user->nation = $nation->name;
                    }
                }

                $users[] = $user;
            }

            $users_sorted_by_role[$role->label] = $users;
        }

        return view('users.index', [
            'users' => $users_sorted_by_role,
            'roles' => $roles,
        ]);
    }

    public function create() {


        $roles = Role::all();

        $academies = Academy::where('is_disabled', false)->get();

        return view('users.create', [
            'roles' => $roles,
            'academies' => $academies,
        ]);
    }

    public function store(Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',

        ]);

        $code_valid = false;

        while (!$code_valid) {
            $unique_code = Str::random(4) . "-" . Str::random(4) . "-" . Str::random(4) . "-" . Str::random(4);
            $code_valid = User::where('unique_code', $unique_code)->count() == 0;
        }

        $nation = Nation::where('name', $request->nationality)->first();
        $user = User::create([
            'name' => $request->name,
            'surname' => $request->surname,
            'email' => $request->email,
            'password' => bcrypt(Str::random(10)),
            'subscription_year' => $request->year,
            'academy_id' => $request->academy_id ?? 0,
            'nation_id' => $nation->id,
            'unique_code' => $unique_code,
        ]);

        $roles = explode(',', $request->roles);

        foreach ($roles as $role) {

            $roleElement = Role::where('label', $role)->first();

            if ($roleElement) {
                $user->roles()->attach($roleElement->id);
            }
        }

        if ($request->academy_id) {
            $academy = Academy::find($request->academy_id);

            if ($user->hasRole('athlete')) {
                $academy->athletes()->attach($user->id);
            } else {
                $academy->personnel()->attach($user->id);
            }
        }



        foreach ($user->allowedRoles() as $role) {
            if (in_array($role, ['rector', 'dean', 'instructor', 'manager'])) {
                $academy->personnel()->attach($user->id);
                break;
            }
        }


        return redirect()->route('users.edit', $user)->with('success', 'User created successfully!');
    }

    public function storeForAcademy(Request $request) {

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
            $user->roles()->attach($role->id);
            $academy->athletes()->attach($user->id);
        } else {

            $roles = explode(',', $request->roles);

            foreach ($roles as $role) {
                $roleElement = Role::where('label', $role)->first();
                if ($roleElement) {
                    $user->roles()->attach($roleElement->id);
                }
            }
            $academy->personnel()->attach($user->id);
        }

        if ($request->go_to_edit === 'on') {
            return redirect()->route('users.edit', $user->id)->with('success', 'User created successfully!');
        } else {
            return redirect()->route('academies.edit', $academy->id)->with('success', 'User created successfully!');
        }
    }

    public function  storeForSchool(Request $request) {
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
            $user->roles()->attach($role->id);
            $academy->athletes()->attach($user->id);
            $school->athletes()->attach($user->id);
        } else {

            $roles = explode(',', $request->roles);

            foreach ($roles as $role) {
                $roleElement = Role::where('label', $role)->first();
                if ($roleElement) {
                    $user->roles()->attach($roleElement->id);
                }
            }
            $academy->personnel()->attach($user->id);
            $school->personnel()->attach($user->id);
        }

        $userRole = auth()->user()->getRole();
        if ($request->go_to_edit === 'on') {
            $redirectRoute = $userRole === 'admin' ? 'users.edit' : $userRole . '.users.edit';
            return redirect()->route($redirectRoute, $user->id)->with('success', 'User created successfully!');
        } else {
            $redirectRoute = $userRole === 'admin' ? 'schools.edit' : $userRole . '.users.edit';
            return redirect()->route('schools.edit', $school->id)->with('success', 'User created successfully!');
        }
    }

    public function  storeForClan(Request $request) {
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
            $user->roles()->attach($role->id);
            $academy->athletes()->attach($user->id);
            $school->athletes()->attach($user->id);
            $clan->users()->attach($user->id);
        } else {

            $roles = explode(',', $request->roles);

            foreach ($roles as $role) {
                $roleElement = Role::where('label', $role)->first();
                if ($roleElement) {
                    $user->roles()->attach($roleElement->id);
                }
            }
            $academy->personnel()->attach($user->id);
            $school->personnel()->attach($user->id);
            $clan->personnel()->attach($user->id);
        }

        if ($request->go_to_edit === 'on') {
            return redirect()->route('users.edit', $user->id)->with('success', 'User created successfully!');
        } else {
            return redirect()->route('clans.edit', $request->clan_id)->with('success', 'User created successfully!');
        }
    }

    public function edit(User $user) {

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
            $user->profile_picture = Storage::disk('gcs')->temporaryUrl(
                $user->profile_picture,
                now()->addMinutes(5)
            );
        }

        $authRole = auth()->user()->getRole();
        $redirectRoute = $authRole === 'admin' ? 'users.edit' :  'users.' . $authRole . '.edit';

        return view($redirectRoute, [
            'user' => $user,
            'academies' => $user->nation->academies ?? [],
            'schools' => $schools,
            'nations' => $countries,
            'roles' => $roles,
        ]);
    }

    public function update(Request $request, User $user) {

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'year' => 'required|integer',
            'nationality' => 'required|string|exists:nations,id',
        ]);

        if ($user->role != 'admin') {
            $user->update([
                'name' => $request->name,
                'surname' => $request->surname,
                'email' => $request->email,
                'subscription_year' => $request->year,
                'nation_id' => $request->nationality,
            ]);
        } else {
            $user->update([
                'name' => $request->name,
                'surname' => $request->surname,
                'email' => $request->email,
                'subscription_year' => $request->year,
                'nation_id' => $request->nationality,
            ]);
        }

        $user->roles()->detach();

        $roles = explode(',', $request->roles);

        foreach ($roles as $role) {

            $roleElement = Role::where('label', $role)->first();

            if ($roleElement) {
                $user->roles()->attach($roleElement->id);
            }
        }

        return redirect()->route('users.index', $user)->with('success', 'User updated successfully!');
    }

    public function destroy(User $user) {
        $user->is_disabled = true;
        $user->save();

        return redirect()->route('users.index')->with('success', 'User disabled successfully!');
    }

    public function search(Request $request) {

        $request->validate([
            'search' => 'required|string',
        ]);

        // $searchTerms = explode(' ', $request->search);
        // $users = User::where(function ($query) use ($searchTerms) {
        //     foreach ($searchTerms as $term) {
        //         $query->orWhere('name', 'like', '%' . $term . '%')
        //             ->orWhere('surname', 'like', '%' . $term . '%');
        //     }
        // })
        //     ->orWhere('email', 'like', '%' . $request->search . '%')
        //     ->with(['roles', 'academies', 'academyAthletes', 'nation'])
        //     ->get();

        $users = User::query()
            ->when($request->search, function (Builder $q, $value) {
                return $q->whereIn('id', User::search($value)->keys());
            })->with(['roles', 'academies', 'academyAthletes', 'nation'])->get();


        return view('users.search-result', [
            'users' => $users,
        ]);
    }

    public function filter() {

        $academies = Academy::where('is_disabled', false)->with('nation')->get();

        return view('users.filter', [
            'academies' => $academies,
        ]);
    }

    public function filterResult(Request $request) {

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


        $filteredUsers = [];

        foreach ($users as $user) {
            $shouldAdd = true;

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

            $user->academy = $user->academyAthletes->first();
            $user->school = $user->schoolAthletes->first();
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

        return view('users.filter-result', [
            'users' => $filteredUsers,
        ]);
    }

    public function setUserRoleForSession(Request $request) {
        $request->validate([
            'role' => 'required|string|exists:roles,label',
        ]);

        $authUser = auth()->user();
        $user = User::find($authUser->id);

        if ($user->hasRole($request->role)) {
            session(['role' => $request->role]);
        } else {
            return back()->with('error', 'You do not have the required role to access this page!');
        }

        return redirect()->route('dashboard');
    }

    public function picture($id, Request $request) {
        if ($request->file('profilepicture') != null) {
            $file = $request->file('profilepicture');
            $file_name = time() . '_' . $file->getClientOriginalName();
            $path = "users/" . $id . "/" . $file_name;

            $storeFile = $file->storeAs("users/" . $id . "/", $file_name, "gcs");

            if ($storeFile) {
                $user = User::find($id);
                $user->profile_picture = $path;
                $user->save();

                return redirect()->route('users.edit', $user->id)->with('success', 'Profile picture uploaded successfully!');
            } else {
                return redirect()->route('users.edit', $id)->with('error', 'Error uploading profile picture!');
            }
        } else {
            return redirect()->route('users.edit', $id)->with('error', 'Error uploading profile picture!');
        }
    }

    public function dashboard(Request $request) {
        $user = auth()->user()->id;
        $user = User::find($user);
        $role = $user->getRole();

        // Modificato per poter poi aggiungere altri ruoli
        switch ($role){
            case 'instructor':
                if (isset($request->course_id)) {
                    return $this->handleInstructor($request->course_id, $user);
                } else {
                    return $this->handleInstructor(0, $user);
                }
            case 'athlete':
                return $this->handleAthlere($user);
            default:
                $view = 'dashboard.' . $role . '.index';
                return view($view);
        }
    }

    private function handleInstructor($course_id = 0, $user) {
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

    private function handleAthlere($user) {

        $announcements = Announcement::where('is_deleted', false)->get();
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
}
