<?php

namespace App\Http\Controllers;

use App\Models\Academy;
use App\Models\Clan;
use App\Models\Nation;
use App\Models\Role;
use App\Models\School;
use App\Models\User;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SchoolController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index() {
        //
        $schools = School::with('nation')->where('is_disabled', '0')->orderBy('created_at', 'desc')->get();

        foreach ($schools as $key => $school) {
            $schools[$key]->nation_name = $school->nation->name;
            $schools[$key]->academy_name = $school->academy->name;
        }

        return view('school.index', [
            'schools' => $schools
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {
        //

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

        return view('school.create', [
            'nations' => $countries,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {
        //

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $slug = Str::slug($request->name);

        if (School::where('slug', $slug)->exists()) {
            $slug = $slug . '-' . time();
        }

        $school = School::create([
            'name' => $request->name,
            'nation_id' =>  $request->nationality,
            'slug' => Str::slug($request->name),
            'academy_id' => $request->academy_id
        ]);

        return redirect()->route('schools.edit', $school)->with('success', 'School created successfully!');
    }

    public function storeacademy(Request $request) {

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $academy = Academy::find($request->academy_id);

        $slug = Str::slug($request->name);

        if (School::where('slug', $slug)->exists()) {
            $slug = $slug . '-' . time();
        }

        $school = School::create([
            'name' => $request->name,
            'nation_id' =>  $academy->nation_id,
            'slug' => $slug,
            'academy_id' => $academy->id
        ]);

        if ($request->go_to_edit_school) {
            return redirect()->route('schools.edit', $school->id)->with('success', 'School created successfully!');
        } else {
            return redirect()->route('academies.edit', $academy->id)->with('success', 'School created successfully!');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(School $school) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(School $school) {
        //

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

        $clans = Clan::whereNotIn('id', $school->clan->pluck('id'))->where('is_disabled', '0')->with(['nation'])->get();
        $associated_athletes = $school->athletes;
        $associated_personnel = $school->personnel;

        $personnel = User::where('is_disabled', '0')->whereNotIn('id', $school->personnel->pluck('id'))->with(['roles'])->get();

        foreach ($personnel as $key => $person) {
            $personnel[$key]->role = implode(', ', $person->roles->pluck('name')->map(function ($role) {
                return __('users.' . $role);
            })->toArray());
        }

        foreach ($associated_personnel as $key => $person) {
            $associated_personnel[$key]->role = implode(', ', $person->roles->pluck('name')->map(function ($role) {
                return __('users.' . $role);
            })->toArray());
        }

        $athletes = User::whereNotIn('id', $school->athletes->pluck('id'))->where('is_disabled', '0')->get();

        $roles = Role::all();

        return view('school.edit', [
            'school' => $school,
            'nations' => $countries,
            'clans' => $clans,
            'personnel' => $personnel,
            'athletes' => $athletes,
            'associated_personnel' => $associated_personnel,
            'associated_athletes' => $associated_athletes,
            'academies' => $school->academy->nation->academies ?? [],
            'roles' => $roles,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, School $school) {
        //

        $request->validate([
            'name' => 'required|string|max:255',
            'nationality' => 'required|integer|exists:nations,id',
            'academy_id' => 'required|integer|exists:academies,id',
        ]);

        $school->update([
            'name' => $request->name,
            'nation_id' => $request->nationality,
            'academy_id' => $request->academy_id,
        ]);

        return redirect()->route('schools.edit', $school)->with('success', 'School updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(School $school) {
        //

        $school->is_disabled = true;
        $school->save();

        return redirect()->route('schools.index')->with('success', 'School disabled successfully!');
    }

    public function addClan(School $school, Request $request) {
        //

        $clan = Clan::find($request->clan_id);
        $clan->school_id = $school->id;

        $clan->save();

        return redirect()->route('schools.edit', $school)->with('success', 'Course added successfully!');
    }

    public function addPersonnel(School $school, Request $request) {
        //

        $personnel = User::find($request->personnel_id);

        $school->personnel()->attach($personnel);


        return redirect()->route('schools.edit', $school)->with('success', 'Personnel added successfully!');
    }

    public function addAthlete(School $school, Request $request) {
        //
        $athlete = User::find($request->athlete_id);

        $school->athletes()->attach($athlete);

        return redirect()->route('schools.edit', $school)->with('success', 'Athlete added successfully!');
    }

    public function all(Request $request) {
        $schools = School::where('is_disabled', '0')->with(['academy'])->get();
        $formatted_schools = [];

        foreach ($schools as $key => $school) {
            $formatted_schools[] = [
                'id' => $school->id,
                'academy' => $school->academy->name,
                'name' => $school->name,
            ];
        }

        return response()->json($formatted_schools);
    }

    public function getByAcademy(Request $request) {

        $academies = json_decode($request->academies);

        $schools = School::whereIn('academy_id', $academies)->where('is_disabled', '0')->with(['academy'])->get();
        $formatted_schools = [];

        foreach ($schools as $key => $school) {
            $formatted_schools[] = [
                'id' => $school->id,
                'academy' => $school->academy->name,
                'name' => $school->name,
            ];
        }

        return response()->json($formatted_schools);
    }

    public function search(Request $request) {
        // $academies = Academy::where('name', 'like', '%' . $request->name . '%')->where('is_disabled', '0')->get();

        $schools = School::query()->when($request->search, function ($q, $search) {
            return $q->whereIn('id', School::search($search)->keys());
        })->where('is_disabled', '0')->with(['academy'])->get();

        $formatted_schools = [];

        foreach ($schools as $key => $school) {
            $formatted_schools[] = [
                'id' => $school->id,
                'academy' => $school->academy->name,
                'name' => $school->name,
            ];
        }


        return response()->json($formatted_schools);
    }

    public function athletesDataForSchool(School $school) {

        $athletes = $school->athletes;
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

    public function athletesClanDataForSchool(School $school) {
        $clans_data = [];

        foreach ($school->clan as $clan) {
            $clans_data[] = [
                'id' => $clan->id,
                'name' => $clan->name,
                'athletes' => $clan->users->count(),
            ];
        }

        return response()->json($clans_data);
    }

    public function getAthletesNumberPerYear(School $school) {
        $athletes = $school->athletes;
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

    public function searchUsers(School $school, Request $request) {
        //

        $roles = json_decode($request->roles);


        $users = User::query()
            ->when($request->search, function (Builder $q, $value) {
                return $q->whereIn('id', User::search($value)->keys());
            })->with(['roles', 'schools', 'schoolAthletes'])->get();

        $users = $users->filter(function ($user) use ($school) {
            $schools = $user->schools->pluck('id')->toArray();
            $schoolAthletes = $user->schoolAthletes->pluck('id')->toArray();

            return in_array($school->id, $schools) || in_array($school->id, $schoolAthletes);
        });

        $filteredUsers = [];


        if ($request->filters_enabled == "true") {

            // Filtro per ruolo 

            if (count($roles) > 0) {

                $users = $users->filter(function ($user) use ($roles) {

                    $allowedRoles = collect($user->allowedRoleIds());

                    foreach ($roles as $id) {
                        if ($allowedRoles->contains($id)) {
                            return true;
                        }
                    }
                });
            }

            // Filtro per data creazione & Filtro per anno iscrizione

            $shouldCheckForCreationDateFrom = $request->from != null;
            $shouldCheckForCreationDateTo = $request->to != null;
            $shouldCheckForYear = $request->year != null;


            if ($request->creation_date) {
                $users = $users->filter(function ($user) use ($request) {
                    return $user->created_at->format('Y-m-d') == $request->creation_date;
                });
            }

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
        } else {
            $filteredUsers = $users;
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
            'backUrl' => route('schools.edit', $school->id),
        ]);
    }
}
