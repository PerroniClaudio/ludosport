<?php

namespace App\Http\Controllers;

use App\Models\Academy;
use App\Models\Nation;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AcademyController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index() {
        //

        $academies = Academy::with('nation')->where('is_disabled', '0')->orderBy('created_at', 'desc')->get();

        foreach ($academies as $key => $academy) {
            $academies[$key]->nation_name = $academy->nation->name;
        }

        return view('academy.index', [
            'academies' => $academies,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {
        //

        $nations = Nation::all();

        return view('academy.create', [
            'nations' => $nations,
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

        $nation = Nation::where('name', $request->nationality)->first();

        $academy = Academy::create([
            'name' => $request->name,
            'nation_id' => $nation->id,
            'slug' => Str::slug($request->name),
        ]);

        return redirect()->route('academies.edit', $academy)->with('success', 'Academy created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Academy $academy) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Academy $academy) {
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

        $schools = School::whereNotIn('id', $academy->schools->pluck('id'))->where('is_disabled', '0')->with(['nation'])->get();
        $personnel = User::where('role', '!=', 'user')->where('is_disabled', '0')->whereNotIn('id', $academy->users->pluck('id'))->get();
        $athletes = User::where('role', '=', 'user')->where('is_disabled', '0')->whereNotIn('id', $academy->users->pluck('id'))->get();

        foreach ($personnel as $key => $person) {
            $personnel[$key]->role = __('users.' . $person->role);
        }

        $associated_personnel = [];
        $associated_athletes = [];

        foreach ($academy->users as $person) {

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



        return view('academy.edit', [
            'academy' => $academy,
            'nations' => $countries,
            'schools' => $schools,
            'personnel' => $personnel,
            'athletes' => $athletes,
            'associated_personnel' => $associated_personnel,
            'associated_athletes' => $associated_athletes,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Academy $academy) {
        //

        $request->validate([
            'name' => 'required|string|max:255',
            'nationality' => 'required|exists:nations,id',
        ]);

        $academy->update([
            'name' => $request->name,
            'nation_id' => $request->nationality,
            'slug' => Str::slug($request->name),
        ]);

        return redirect()->route('academies.index', $academy)->with('success', 'Academy updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Academy $academy) {
        //

        $academy->is_disabled = true;
        $academy->save();

        return redirect()->route('academies.index')->with('success', 'Academy disabled successfully!');
    }

    public function schools(Academy $academy) {
        return response()->json($academy->schools);
    }

    public function addSchool(Request $request, Academy $academy) {
        $school = School::find($request->school_id);
        $school->academy_id = $academy->id;
        $school->save();

        return redirect()->route('academies.edit', $academy)->with('success', 'School added successfully!');
    }

    public function addPersonnel(Request $request, Academy $academy) {
        $personnel = User::find($request->personnel_id);

        if ($personnel->role === "user") {
            return redirect()->route('academies.edit', $academy)->with('error', 'Use this function for personnel only!');
        }

        $personnel->academy_id = $academy->id;
        $personnel->save();

        return redirect()->route('academies.edit', $academy)->with('success', 'Personnel added successfully!');
    }

    public function addAthlete(Request $request, Academy $academy) {
        $athlete = User::find($request->athlete_id);

        if ($athlete->role !== "user") {
            return redirect()->route('academies.edit', $academy)->with('error', 'Use this function for athletes only!');
        }

        $athlete->academy_id = $academy->id;
        $athlete->save();

        return redirect()->route('academies.edit', $academy)->with('success', 'Athlete added successfully!');
    }
}
