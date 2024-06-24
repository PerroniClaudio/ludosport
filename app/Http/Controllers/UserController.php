<?php

namespace App\Http\Controllers;

use App\Models\Academy;
use App\Models\Nation;
use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserController extends Controller {

    public function index() {


        $roles = Role::all();

        $users_sorted_by_role = [];


        foreach ($roles as $role) {

            $users = [];

            foreach ($role->users as $user) {
                if ($user->is_disabled) {
                    continue;
                }

                $users[] = $user;
            }

            $users_sorted_by_role[$role->label] = $users;
        }

        return view('users.index', [
            'users' => $users_sorted_by_role,
            'roles' => $roles,
        ]);
    }

    public function create() {


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

        $code_valid = false;

        while (!$code_valid) {
            $unique_code = Str::random(4) . "-" . Str::random(4) . "-" . Str::random(4) . "-" . Str::random(4);
            $code_valid = User::where('unique_code', $unique_code)->count() == 0;
        }

        $nation = Nation::where('name', $request->nationality)->first();
        $user = User::create([
            'name' => $request->name,
            'surname' => $request->surname,
            'email' => $request->email,
            'password' => bcrypt(Str::random(10)),
            'subscription_year' => $request->year,
            'academy_id' => $request->academy_id ?? 0,
            'nation_id' => $nation->id,
            'unique_code' => $unique_code,
        ]);

        $roles = explode(',', $request->roles);

        foreach ($roles as $role) {

            $roleElement = Role::where('label', $role)->first();

            if ($roleElement) {
                $user->roles()->attach($roleElement->id);
            }
        }

        if ($request->academy_id) {
            $academy = Academy::find($request->academy_id);

            if ($user->hasRole('athlete')) {
                $academy->athletes()->attach($user->id);
            }
        }



        foreach ($user->allowedRoles() as $role) {
            if (in_array($role, ['rector', 'dean', 'instructor', 'manager'])) {
                $academy->personnel()->attach($user->id);
                break;
            }
        }


        return redirect()->route('users.edit', $user)->with('success', 'User created successfully!');
    }

    public function edit(User $user) {

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

        $roles = Role::all();
        $user->roles = $user->roles->pluck('label')->toArray();

        $user->profile_picture = Storage::disk('gcs')->temporaryUrl(
            $user->profile_picture,
            now()->addMinutes(5)
        );


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
        ]);

        if ($user->role != 'admin') {
            $user->update([
                'name' => $request->name,
                'surname' => $request->surname,
                'email' => $request->email,
                'subscription_year' => $request->year,
                'nation_id' => $request->nationality,
            ]);
        } else {
            $user->update([
                'name' => $request->name,
                'surname' => $request->surname,
                'email' => $request->email,
                'subscription_year' => $request->year,
                'nation_id' => $request->nationality,
            ]);
        }

        $user->roles()->detach();

        $roles = explode(',', $request->roles);

        foreach ($roles as $role) {

            $roleElement = Role::where('label', $role)->first();

            if ($roleElement) {
                $user->roles()->attach($roleElement->id);
            }
        }

        return redirect()->route('users.index', $user)->with('success', 'User updated successfully!');
    }

    public function destroy(User $user) {
        $user->is_disabled = true;
        $user->save();

        return redirect()->route('users.index')->with('success', 'User disabled successfully!');
    }

    public function search(Request $request) {

        $request->validate([
            'search' => 'required|string',
        ]);



        // $searchTerms = explode(' ', $request->search);
        // $users = User::where(function ($query) use ($searchTerms) {
        //     foreach ($searchTerms as $term) {
        //         $query->orWhere('name', 'like', '%' . $term . '%')
        //             ->orWhere('surname', 'like', '%' . $term . '%');
        //     }
        // })
        //     ->orWhere('email', 'like', '%' . $request->search . '%')
        //     ->with(['roles', 'academies', 'academyAthletes', 'nation'])
        //     ->get();



        $users = User::query()
            ->when($request->search, function (Builder $q, $value) {
                return $q->whereIn('id', User::search($value)->keys());
            })->with(['roles', 'academies', 'academyAthletes', 'nation'])->get();


        return view('users.search-result', [
            'users' => $users,
        ]);
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

    public function picture($id, Request $request) {
        if ($request->file('profilepicture') != null) {
            $file = $request->file('profilepicture');
            $file_name = time() . '_' . $file->getClientOriginalName();
            $path = "users/" . $id . "/" . $file_name;

            $storeFile = $file->storeAs("users/" . $id . "/", $file_name, "gcs");

            if ($storeFile) {
                $user = User::find($id);
                $user->profile_picture = $path;
                $user->save();

                return redirect()->route('users.edit', $user->id)->with('success', 'Profile picture uploaded successfully!');
            } else {
                return redirect()->route('users.edit', $id)->with('error', 'Error uploading profile picture!');
            }
        } else {
            return redirect()->route('users.edit', $id)->with('error', 'Error uploading profile picture!');
        }
    }
}
