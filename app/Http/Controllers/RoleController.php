<?php

namespace App\Http\Controllers;

use App\Models\CustomRole;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

class RoleController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index() {
        //

        return response()->json(CustomRole::all());
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

        $request->validate([
            'name' => 'required|unique:custom_roles|max:255',
            'user_id' => 'required|exists:users,id'
        ]);

        $user = User::find($request->user_id);

        $customRole = CustomRole::create([
            'name' => strtolower($request->name)
        ]);

        $user->customRoles()->detach();
        $user->customRoles()->attach($customRole->id);

        return response()->json($customRole);
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role) {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role) {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role) {
        //
    }

    public function search(Request $request) {

        $request->validate([
            'name' => 'required'
        ]);

        $needle = strtolower($request->name);

        $customRoles = CustomRole::where('name', 'like', '%' . $needle . '%')->get();

        return response()->json($customRoles);
    }

    public function assign(Request $request) {

        $request->validate([
            'role_id' => 'required|exists:custom_roles,id',
            'user_id' => 'required|exists:users,id'
        ]);

        $user = User::find($request->user_id);
        $customRole = CustomRole::find($request->role_id);

        $user->customRoles()->detach();
        $user->customRoles()->attach($customRole->id);

        return response()->json($customRole);
    }
}
