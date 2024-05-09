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

        return redirect()->route('users.show', $user)->with('success', 'Utente creato con successo!');
    }

    public function show(User $user)
    {

        $academies = Academy::all();

        return view('users.show', [
            'user' => $user,
            'academies' => $academies,
        ]);
    }

}
