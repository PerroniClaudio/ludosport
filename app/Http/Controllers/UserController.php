<?php

namespace App\Http\Controllers;

use App\Models\Academy;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UserController extends Controller
{

    
   
    public function index()
    {
        return view('users.index');
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
            'password' => 'required|string|min:8',
            'role' => 'required|string|in:admin,editor,viewer',
        ]);

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $password = Str::password();
        $user->password = bcrypt($password);
        $user->role = $request->role;
        $user->save();

        return redirect()->route('users.index');
    }

}
