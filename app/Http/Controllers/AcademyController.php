<?php

namespace App\Http\Controllers;

use App\Models\Academy;
use App\Models\Nation;
use App\Models\Role;
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
        $associated_athletes = $academy->athletes;
        $associated_personnel = $academy->personnel;

        $personnel = User::where('is_disabled', '0')->whereNotIn('id', $academy->personnel->pluck('id'))->with(['roles'])->get();

        foreach ($personnel as $key => $person) {
            $personnel[$key]->role = implode(', ', $person->roles->pluck('name')->map(function ($role) {
                return __('users.' . $role);
            })->toArray());
        }

        $athletes = User::whereNotIn('id', $academy->athletes->pluck('id'))->where('is_disabled', '0')->get();

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

        $academy->personnel()->attach($personnel);

        return redirect()->route('academies.edit', $academy)->with('success', 'Personnel added successfully!');
    }

    public function addAthlete(Request $request, Academy $academy) {
        $athlete = User::find($request->athlete_id);

        $academy->athletes()->attach($athlete);

        return redirect()->route('academies.edit', $academy)->with('success', 'Athlete added successfully!');
    }

    public function all(Request $request) {
        $academies = Academy::where('is_disabled', '0')->with(['nation'])->get();
        $formatted_academies = [];

        foreach ($academies as $key => $academy) {
            $formatted_academies[] = [
                'id' => $academy->id,
                'nation' => $academy->nation->name,
                'name' => $academy->name,
            ];
        }

        return response()->json($formatted_academies);
    }

    public function search(Request $request) {
        // $academies = Academy::where('name', 'like', '%' . $request->name . '%')->where('is_disabled', '0')->get();

        $academies = Academy::query()->when($request->search, function ($q, $search) {
            return $q->whereIn('id', Academy::search($search)->keys());
        })->where('is_disabled', '0')->with(['nation'])->get();

        $formatted_academies = [];

        foreach ($academies as $key => $academy) {
            $formatted_academies[] = [
                'id' => $academy->id,
                'nation' => $academy->nation->name,
                'name' => $academy->name,
            ];
        }


        return response()->json($formatted_academies);
    }
}
