<?php

namespace App\Http\Controllers;

use App\Models\Academy;
use App\Models\Nation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UserController extends Controller
{

    public function index()
    {

        $users = User::orderBy('created_at', 'desc')->get();

        return view('users.index', [
            'users' => $users,
        ]);
    }

    public function create()
    {

        $user = new User();
        $roles = $user->getAllowedRolesWithoutAdmin();
        $roles = array_map(function ($role) {
            return [
                'value' => $role,
                'label' => __("users.$role"),
            ];
        }, $roles);


        $academies = Academy::all();
     
        return view('users.create', [
            'roles' => $roles,
            'academies' => $academies,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|string|in:user,rettore,preside,manager,tecnico,istruttore',
        ]);

        $nation = Nation::where('name', $request->nationality)->first();
        $user = User::create([
            'name' => $request->name,
            'surname' => $request->surname,
            'email' => $request->email,
            'password' => bcrypt(Str::random(10)),
            'role' => $request->role,
            'subscription_year' => $request->year,
            'academy_id' => $request->academy_id,
            'nation_id' => $nation->id,
        ]);

        return redirect()->route('users.show', $user)->with('success', 'User created successfully!');
    }

    public function edit(User $user)
    {

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

        if($user->academy) {
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

    public function update(Request $request, User $user)
    {

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'year' => 'required|integer',
            'nationality' => 'required|string|exists:nations,id',
            'academy_id' => 'required|integer|exists:academies,id',
            'school_id' => 'required|integer|exists:schools,id',
        ]);

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

        return redirect()->route('users.index', $user)->with('success', 'User updated successfully!');

    }

    public function destroy(User $user)
    {
        $user->is_disabled = true;
        $user->save();

        return redirect()->route('users.index')->with('success', 'User disabled successfully!');
    }

}
