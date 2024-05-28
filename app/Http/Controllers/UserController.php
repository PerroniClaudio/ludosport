<?php

namespace App\Http\Controllers;

use App\Models\Academy;
use App\Models\Nation;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UserController extends Controller {

    public function index() {

        $users = User::orderBy('created_at', 'desc')->get();
        $roles = Role::all();

        $users_sorted_by_role = [];

        foreach ($roles as $role) {
            $users_sorted_by_role[$role->label] = $role->users;
        }

        return view('users.index', [
            'users' => $users_sorted_by_role,
            'roles' => $roles,
        ]);
    }

    public function create() {

        $user = new User();
        $roles = Role::all();

        $academies = Academy::all();

        return view('users.create', [
            'roles' => $roles,
            'academies' => $academies,
        ]);
    }

    public function store(Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',

        ]);

        $nation = Nation::where('name', $request->nationality)->first();
        $user = User::create([
            'name' => $request->name,
            'surname' => $request->surname,
            'email' => $request->email,
            'password' => bcrypt(Str::random(10)),
            'subscription_year' => $request->year,
            'academy_id' => $request->academy_id ?? 0,
            'nation_id' => $nation->id,
        ]);

        $roles = explode(',', $request->roles);

        foreach ($roles as $role) {

            $roleElement = Role::where('label', $role)->first();

            if ($roleElement) {
                $user->roles()->attach($roleElement->id);
            }
        }


        return redirect()->route('users.edit', $user)->with('success', 'User created successfully!');
    }

    public function edit(User $user) {

        $roles = $user->getAllowedRolesWithoutAdmin();
        $roles = array_map(function ($role) {
            return [
                'value' => $role,
                'label' => __("users.$role"),
            ];
        }, $roles);

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

        if ($user->academy) {
            $academy = Academy::find($user->academy->id);
            $schools = $academy->schools;
        } else {
            $schools = [];
        }

        $user->is_verified = $user->email_verified_at ? true : false;

        return view('users.edit', [
            'user' => $user,
            'academies' => $user->nation->academies ?? [],
            'schools' => $schools,
            'nations' => $countries,
            'roles' => $roles,
        ]);
    }

    public function update(Request $request, User $user) {

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'year' => 'required|integer',
            'nationality' => 'required|string|exists:nations,id',
            'academy_id' => 'required|integer|exists:academies,id',
            'school_id' => 'required|integer|exists:schools,id',
        ]);

        if ($user->role != 'admin') {
            $user->update([
                'name' => $request->name,
                'surname' => $request->surname,
                'email' => $request->email,
                'subscription_year' => $request->year,
                'nation_id' => $request->nationality,
                'academy_id' => $request->academy_id,
                'school_id' => $request->school_id,
                'role' => $request->role ?? 'user'
            ]);
        } else {
            $user->update([
                'name' => $request->name,
                'surname' => $request->surname,
                'email' => $request->email,
                'subscription_year' => $request->year,
                'nation_id' => $request->nationality,
                'academy_id' => $request->academy_id,
                'school_id' => $request->school_id,
            ]);
        }


        return redirect()->route('users.index', $user)->with('success', 'User updated successfully!');
    }

    public function destroy(User $user) {
        $user->is_disabled = true;
        $user->save();

        return redirect()->route('users.index')->with('success', 'User disabled successfully!');
    }

    public function setUserRoleForSession(Request $request) {
        $request->validate([
            'role' => 'required|string|exists:roles,label',
        ]);

        $authUser = auth()->user();
        $user = User::find($authUser->id);

        if ($user->hasRole($request->role)) {
            session(['role' => $request->role]);
        } else {
            return back()->with('error', 'You do not have the required role to access this page!');
        }


        return redirect()->route('dashboard');
    }
}
