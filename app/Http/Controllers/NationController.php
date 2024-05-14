<?php

namespace App\Http\Controllers;

use App\Models\Academy;
use App\Models\Nation;
use Illuminate\Http\Request;

class NationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $nations = Nation::all();

        foreach ($nations as $nation) {
            $continents[$nation['continent']][] = ['id' => $nation['id'], 'name' => $nation['name'], 'code' => $nation['code']];
        }

        $continents = [
            'Europe' => $continents['Europe'],
            'Africa' => $continents['Africa'],
            'Asia' => $continents['Asia'],
            'North America' => $continents['North America'],
            'Oceania' => $continents['Oceania'],
        ];

        
        return view('nation.index', [
            'continents' => $continents,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Nation $nation)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Nation $nation)
    {
        //

        $academies = Academy::whereNotIn('id', $nation->academies->pluck('id'))->with('nation')->get();


       return view('nation.edit', [
            'nation' => $nation,
            'academies' => $academies,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Nation $nation)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Nation $nation)
    {
        //
    }

    public function academies(Nation $nation)
    {
        //
        return response($nation->academies);
    }

    public function associateAcademy(Nation $nation, Request $request)
    {
        //
        $academy = Academy::find($request->academy_id);
        $academy->nation_id = $nation->id;
        $academy->save();

        return redirect()->route('nations.edit', $nation->id)->with('success', 'Academy associated successfully!');
    }


}
