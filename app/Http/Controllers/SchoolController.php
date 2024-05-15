<?php

namespace App\Http\Controllers;

use App\Models\Clan;
use App\Models\Nation;
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
        $schools = School::with('nation')->orderBy('created_at', 'desc')->get();

        foreach ($schools as $key => $school) {
            $schools[$key]->nation_name = $school->nation->name;
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

        $school = School::create([
            'name' => $request->name,
            'nation_id' =>  $request->nationality,
            'slug' => Str::slug($request->name),
            'academy_id' => $request->academy_id
        ]);

        return redirect()->route('schools.edit', $school)->with('success', 'School created successfully!');
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
            'Oceania' => $countries['Oceania'],
        ];

        $clans = Clan::whereNotIn('id', $school->clan->pluck('id'))->with(['nation'])->get();
        $personnel = User::where('role', '!=', 'user')->whereNotIn('id', $school->users->pluck('id'))->get();
        $athletes = User::where('role', '=', 'user')->whereNotIn('id', $school->users->pluck('id'))->get();

        foreach ($personnel as $key => $person) {
            $personnel[$key]->role = __('users.' . $person->role);
        }

        $associated_personnel = [];
        $associated_athletes = [];

        foreach ($school->users as $person) {

            if ($person->role === "user") {
                $associated_athletes[] = [
                    'id' => $person->id,
                    'name' => $person->name,
                    'surname' => $person->surname,
                ];
                continue;
            }

            $associated_personnel[] = [
                'id' => $person->id,
                'name' => $person->name,
                'surname' => $person->surname,
                'role' => __('users.' . $person->role),
            ];
        }

        return view('school.edit', [
            'school' => $school,
            'nations' => $countries,
            'clans' => $clans,
            'personnel' => $personnel,
            'athletes' => $athletes,
            'associated_personnel' => $associated_personnel,
            'associated_athletes' => $associated_athletes,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, School $school) {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(School $school) {
        //
    }

    public function addClan(School $school, Request $request) {
        //
    }

    public function addPersonnel(School $school, Request $request) {
        //

        $personnel = User::find($request->personnel_id);

        if ($personnel->role === "user") {
            return redirect()->route('schools.edit', $school)->with('error', 'Use this function for personnel only!');
        }

        $personnel->school_id = $school->id;
        $personnel->save();

        return redirect()->route('schools.edit', $school)->with('success', 'Personnel added successfully!');
    }

    public function addAthlete(School $school, Request $request) {
        //
        $athlete = User::find($request->athlete_id);

        if ($athlete->role !== "user") {
            return redirect()->route('schools.edit', $school)->with('error', 'Use this function for athletes only!');
        }

        $athlete->school_id = $school->id;
        $athlete->save();

        return redirect()->route('schools.edit', $school)->with('success', 'Athlete added successfully!');
    }
}
