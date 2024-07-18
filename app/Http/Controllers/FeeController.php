<?php

namespace App\Http\Controllers;

use App\Models\Fee;
use App\Models\User;
use Illuminate\Http\Request;

class FeeController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index() {
        //

        $user = User::find(Auth()->user()->id);
        $academy = $user->academies()->first()->id;

        $fees = Fee::where('academy_id', $academy)->get();

        return view('fees.rector.index', [
            'fees' => $fees,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {
        //

        $user = User::find(Auth()->user()->id);
        $academy = $user->academies()->first()->id;

        return view('fees.rector.create', [
            'academy' => $academy,
        ]);
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
    public function show(Fee $fee) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Fee $fee) {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Fee $fee) {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Fee $fee) {
        //
    }
}
