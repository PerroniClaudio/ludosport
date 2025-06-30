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

        // Handle sorting
        $sortBy = $request->get('sortedby', 'created_at');
        $sortDirection = $request->get('direction', 'asc');

        // Ensure valid direction
        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'asc';
        }

        // Define valid sort fields for each role
        $validSortFields = [
            'athlete' => ['id', 'name', 'surname', 'email', 'subscription_year', 'nation', 'academy', 'school', 'has_paid_fee', 'created_at'],
            'instructor' => ['id', 'name', 'email', 'weapon_forms', 'created_at'],
            'technician' => ['id', 'name', 'email', 'weapon_forms', 'created_at'],
            'rector' => ['id', 'name', 'email', 'academy', 'created_at'],
            'dean' => ['id', 'name', 'email', 'school', 'created_at'],
            'default' => ['id', 'name', 'email', 'created_at']
        ];

        // Get valid fields for current role
        $currentRoleFields = $validSortFields[$selectedRole] ?? $validSortFields['default'];

        // Validate sort field
        if (!in_array($sortBy, $currentRoleFields)) {
            $sortBy = 'created_at';
        }

        // Apply specific relations and selections based on role
        switch ($selectedRole) {
            case 'athlete':
                $query = $baseQuery
                    ->select('*')
                    ->with([
                        'academyAthletes',
                        'schoolAthletes',
                        'nation'
                    ]);

                // Handle special sorting for athletes
                switch ($sortBy) {
                    case 'nation':
                        $query->leftJoin('nations', 'users.nation_id', '=', 'nations.id')
                            ->orderBy('nations.name', $sortDirection);
                        break;
                    case 'academy':
                        $query->leftJoin('academy_athletes', 'users.id', '=', 'academy_athletes.athlete_id')
                            ->leftJoin('academies', function ($join) {
                                $join->on('academy_athletes.academy_id', '=', 'academies.id')
                                    ->where('academy_athletes.is_primary', '=', true);
                            })
                            ->orderBy('academies.name', $sortDirection);
                        break;
                    case 'school':
                        $query->leftJoin('school_athletes', 'users.id', '=', 'school_athletes.athlete_id')
                            ->leftJoin('schools', function ($join) {
                                $join->on('school_athletes.school_id', '=', 'schools.id')
                                    ->where('school_athletes.is_primary', '=', true);
                            })
                            ->orderBy('schools.name', $sortDirection);
                        break;
                    default:
                        $query->orderBy($sortBy, $sortDirection);
                        break;
                }

                $users = $query->paginate(10);
                break;

            case 'instructor':
                $query = $baseQuery
                    ->select('*')
                    ->with(['weaponFormsPersonnel']);

                // Handle special sorting for instructors
                if ($sortBy === 'weapon_forms') {
                    // For weapon forms, we'll sort by the count of weapon forms
                    $query->withCount('weaponFormsPersonnel')
                        ->orderBy('weapon_forms_personnel_count', $sortDirection);
                } else {
                    $query->orderBy($sortBy, $sortDirection);
                }

                $users = $query->paginate(10);
                break;

            case 'technician':
                $query = $baseQuery
                    ->select('*')
                    ->with(['weaponFormsTechnician']);

                // Handle special sorting for technicians
                if ($sortBy === 'weapon_forms') {
                    // For weapon forms, we'll sort by the count of weapon forms
                    $query->withCount('weaponFormsTechnician')
                        ->orderBy('weapon_forms_technician_count', $sortDirection);
                } else {
                    $query->orderBy($sortBy, $sortDirection);
                }

                $users = $query->paginate(10);
                break;

            case 'rector':
                $query = $baseQuery
                    ->select('*')
                    ->with(['academies']);

                // Handle special sorting for rectors
                if ($sortBy === 'academy') {
                    $query->leftJoin('academy_users', 'users.id', '=', 'academy_users.user_id')
                        ->leftJoin('academies', function ($join) {
                            $join->on('academy_users.academy_id', '=', 'academies.id')
                                ->where('academy_users.is_primary', '=', true);
                        })
                        ->orderBy('academies.name', $sortDirection);
                } else {
                    $query->orderBy($sortBy, $sortDirection);
                }

                $users = $query->paginate(10);
                break;

            case 'dean':
                $query = $baseQuery
                    ->select('*')
                    ->with(['schools']);

                // Handle special sorting for deans
                if ($sortBy === 'school') {
                    $query->leftJoin('school_users', 'users.id', '=', 'school_users.user_id')
                        ->leftJoin('schools', function ($join) {
                            $join->on('school_users.school_id', '=', 'schools.id')
                                ->where('school_users.is_primary', '=', true);
                        })
                        ->orderBy('schools.name', $sortDirection);
                } else {
                    $query->orderBy($sortBy, $sortDirection);
                }

                $users = $query->paginate(10);
                break;

            case 'manager':
            case 'admin':
            default:
                $users = $baseQuery
                    ->select('*')
                    ->with(['roles'])
                    ->orderBy($sortBy, $sortDirection)
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
            'currentSort' => $sortBy,
            'currentDirection' => $sortDirection,
        ]);
    }
}
