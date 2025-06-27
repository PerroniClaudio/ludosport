<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Models\Nation;
use Illuminate\Http\Request;

class PaginatedUserController extends Controller {
    public function index(Request $request) {
        $roles = Role::all();
        $selectedRole = $request->role ? $request->role : 'athlete';

        // Base query with common conditions
        $baseQuery = User::query()
            ->where('is_disabled', false)
            ->when($request->role, function ($query) use ($request) {
                return $query->whereHas('roles', function ($q) use ($request) {
                    $q->where('label', $request->role);
                });
            });

        // Apply specific relations and selections based on role
        switch ($selectedRole) {
            case 'athlete':
                $users = $baseQuery
                    ->select('*')
                    ->with([
                        'academyAthletes',
                        'schoolAthletes',
                        'nation'
                    ])
                    ->orderBy('created_at', 'desc')
                    ->paginate(10);
                break;

            case 'instructor':
                $users = $baseQuery
                    ->select('*')
                    ->with(['weaponFormsPersonnel'])
                    ->orderBy('created_at', 'desc')
                    ->paginate(10);
                break;

            case 'technician':
                $users = $baseQuery
                    ->select('*')
                    ->with(['weaponFormsTechnician'])
                    ->orderBy('created_at', 'desc')
                    ->paginate(10);
                break;

            case 'rector':
                $users = $baseQuery
                    ->select('*')
                    ->with(['academies'])
                    ->orderBy('created_at', 'desc')
                    ->paginate(10);
                break;

            case 'dean':
                $users = $baseQuery
                    ->select('*')
                    ->with(['schools'])
                    ->orderBy('created_at', 'desc')
                    ->paginate(10);
                break;

            case 'manager':
            case 'admin':
            default:
                $users = $baseQuery
                    ->select('*')
                    ->with(['roles'])
                    ->orderBy('created_at', 'desc')
                    ->paginate(10);
                break;
        }

        // Format additional data based on selected role
        foreach ($users as $user) {
            switch ($selectedRole) {
                case 'athlete':
                    // Format nation name
                    if ($user->nation_id === null) {
                        $user->nation = "Not set";
                    } else {
                        $user->nation = $user->nation ? $user->nation->name : "Not set";
                    }
                    break;

                case 'instructor':
                    // Format weapon forms for instructors
                    $user->weapon_forms_instructor_formatted = $user->weaponFormsPersonnel()->pluck('name')->toArray();
                    break;

                case 'technician':
                    // Format weapon forms for technicians
                    $user->weapon_forms_technician_formatted = $user->weaponFormsTechnician()->pluck('name')->toArray();
                    break;

                case 'rector':
                    // Format primary academy for rectors
                    $user->primary_academy = $user->primaryAcademy() ? $user->primaryAcademy()->name : "No academy";
                    break;

                case 'dean':
                    // Format primary school for deans
                    $user->primary_school = $user->primarySchool() ? $user->primarySchool()->name : "No school";
                    break;
            }
        }

        return view('users.paginated', [
            'roles' => $roles,
            'selectedRole' => $selectedRole,
            'users' => $users,
        ]);
    }
}
