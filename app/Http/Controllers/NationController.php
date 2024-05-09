<?php

namespace App\Http\Controllers;

use App\Models\Nation;
use Illuminate\Http\Request;

class NationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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


}
