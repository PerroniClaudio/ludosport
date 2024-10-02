<?php

namespace App\Http\Controllers;

use App\Models\Academy;
use App\Models\Nation;
use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class NationController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index() {
        $nations = Nation::all();

        foreach ($nations as $nation) {
            $continents[$nation['continent']][] = ['id' => $nation['id'], 'name' => $nation['name'], 'code' => $nation['code']];
        }

        $continents = [
            'Europe' => $continents['Europe'],
            'Africa' => $continents['Africa'],
            'Asia' => $continents['Asia'],
            'North America' => $continents['North America'],
            'South America' => $continents['South America'],
            'Oceania' => $continents['Oceania'],
        ];


        return view('nation.index', [
            'continents' => $continents,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Nation $nation) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Nation $nation) {
        //

        $roles = Role::all();
        $academies = Academy::whereNotIn('id', $nation->academies->pluck('id'))->with('nation')->get();

        $nation->flag = Storage::disk('gcs')->temporaryUrl(
            $nation->flag,
            now()->addMinutes(5)
        );

        $nation->academies = $nation->academies()->orderBy('id', 'desc')->get();

        return view('nation.edit', [
            'nation' => $nation,
            'academies' => $academies,
            'roles' => $roles,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Nation $nation) {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Nation $nation) {
        //
    }

    public function academies(Nation $nation) {
        //
        return response($nation->academies);
    }

    public function associateAcademy(Nation $nation, Request $request) {
        //
        $academy = Academy::find($request->academy_id);
        $academy->nation_id = $nation->id;
        $academy->save();

        return redirect()->route('nations.edit', $nation->id)->with('success', 'Academy associated successfully!');
    }

    public function updateFlag($id, Request $request) {
        //
        if ($request->file('flag') != null) {
            $file = $request->file('flag');
            $file_name = time() . '_' . $file->getClientOriginalName();
            $path = "nations/" . $id . "/" . $file_name;

            $storeFile = $file->storeAs("nations/" . $id . "/", $file_name, "gcs");

            if ($storeFile) {
                $nation = Nation::find($id);
                $nation->flag = $path;
                $nation->save();

                return redirect()->route('nations.edit', $nation->id)->with('success', 'Flag uploaded successfully!');
            } else {
                return redirect()->route('nations.edit', $id)->with('error', 'Error uploading flag!');
            }
        } else {
            return redirect()->route('nations.edit', $id)->with('error', 'Error uploading flag!');
        }
    }

    public function searchUsers(Nation $nation, Request $request) {
        //

        $roles = json_decode($request->roles);


        $users = User::query()
            ->when($request->search, function (Builder $q, $value) {
                return $q->whereIn('id', User::search($value)->keys());
            })->with(['roles', 'nation'])->get();

        $users = $users->filter(function ($user) use ($nation) {
            return $user->nation->id == $nation->id;
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

        return view('users.filter-result', [
            'users' => $filteredUsers,
            'backUrl' => route('nations.edit', $nation->id),
        ]);
    }

    public function getNationAthletesNumberPerYear(Nation $nation) {
        
        $athletes = User::where('is_disabled', false)->whereHas('roles', function ($q) {
            $q->where('label', 'athlete');
        })->whereHas('academyAthletes', function ($q) use ($nation) {
            $q->where('nation_id', $nation->id);
        } )->get();
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
