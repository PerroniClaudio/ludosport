<?php

namespace App\Http\Controllers;

use App\Models\Academy;
use App\Models\Clan;
use App\Models\Nation;
use App\Models\Role;
use App\Models\School;
use App\Models\User;
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
}
