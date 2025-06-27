<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Models\Nation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaginatedUserController extends Controller {
    public function index(Request $request) {
        $authUser = User::find(Auth::user()->id);
        $authUserRole = $authUser->getRole();

        if (!in_array($authUserRole, ['admin', 'rector', 'dean', 'manager', 'technician', 'instructor'])) {
            return redirect()->route("dashboard")->with('error', 'You do not have the required role to access this page!');
        }

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

        // Apply scope filtering based on auth user role
        switch ($authUserRole) {
            case 'admin':
            case 'technician':
                // Tutti gli utenti - nessun filtro aggiuntivo
                break;

            case 'manager':
            case 'rector':
                // Utenti di una determinata accademia
                $academy_id = $authUser->primaryAcademy()->id ?? null;
                if (!$academy_id) {
                    return redirect()->route("dashboard")->with('error', 'You don\'t have an academy assigned!');
                }

                $baseQuery->where(function ($query) use ($academy_id) {
                    $query->whereHas('academies', function ($q) use ($academy_id) {
                        $q->where('academy_id', $academy_id);
                    })->orWhereHas('academyAthletes', function ($q) use ($academy_id) {
                        $q->where('academy_id', $academy_id);
                    });
                });
                break;

            case 'instructor':
                // Utenti di tutte le accademie in cui ha un corso
                $academiesIds = $authUser->academies()->pluck('academy_id')->toArray();

                $baseQuery->where(function ($query) use ($academiesIds) {
                    $query->whereHas('academies', function ($q) use ($academiesIds) {
                        $q->whereIn('academy_id', $academiesIds);
                    })->orWhereHas('academyAthletes', function ($q) use ($academiesIds) {
                        $q->whereIn('academy_id', $academiesIds);
                    });
                });
                break;

            case 'dean':
                // Utenti di una determinata scuola
                $school_id = $authUser->primarySchool()->id ?? null;

                if (!$school_id) {
                    return redirect()->route("dashboard")->with('error', 'You don\'t have a school assigned!');
                }

                $baseQuery->where(function ($query) use ($school_id) {
                    $query->whereHas('schools', function ($q) use ($school_id) {
                        $q->where('school_id', $school_id);
                    })->orWhereHas('schoolAthletes', function ($q) use ($school_id) {
                        $q->where('school_id', $school_id);
                    });
                });
                break;

            default:
                return redirect()->route("dashboard")->with('error', 'You are not authorized to access this page!');
        }

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
