<?php

namespace App\Http\Controllers;

use App\Models\Clan;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ClanController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index() {
        //

        $clans = Clan::orderBy('created_at', 'desc')->where('is_disabled', '0')->with(['school'])->get();

        foreach ($clans as $key => $clan) {
            $clans[$key]->school_name = $clan->school->name;
        }

        return view('clan.index', [
            'clans' => $clans
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {
        //

        $schools = School::all();
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

        return view('clan.create', [
            'schools' => $formatted_schools
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

        $clan = Clan::create([
            'name' => $request->name,
            'school_id' => $request->school_id,
            'slug' => Str::slug($request->name)
        ]);

        return redirect()->route('clans.edit', $clan)->with('success', 'Course created successfully.');
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
        //

        $schools = School::all();
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

        $instructors = User::whereHas('roles', function ($query) {
            $query->where('label', 'instructor');
        })->whereNotIn('id', $clan->personnel->pluck('id'))->get();

        $athletes = User::where('role', 'user')->where('is_disabled', '0')->whereNotIn('id', $clan->users->pluck('id'))->get();


        return view('clan.edit', [
            'clan' => $clan,
            'schools' => $formatted_schools,
            'associated_instructors' => $associated_instructors,
            'instructors' => $instructors,
            'associated_athletes' => $associated_athletes,
            'athletes' => $athletes
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Clan $clan) {
        //

        $request->validate([
            'name' => 'required',
            'school_id' => 'required',
        ]);

        $clan->update([
            'name' => $request->name,
            'school_id' => $request->school_id,
            'slug' => Str::slug($request->name)
        ]);

        return redirect()->route('clans.edit', $clan)->with('success', 'Course updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Clan $clan) {
        //

        $clan->is_disabled = true;
        $clan->save();

        return redirect()->route('clans.index')->with('success', 'Course disabled successfully.');
    }

    public function addInstructor(Clan $clan, Request $request) {
        //
        $clan->personnel()->attach($request->instructor_id);

        return redirect()->route('clans.edit', $clan)->with('success', 'Instructor added successfully.');
    }

    public function addAthlete(Clan $clan, Request $request) {
        //

        $clan->users()->attach($request->athlete_id);

        return redirect()->route('clans.edit', $clan)->with('success', 'Athlete added successfully.');
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
}
