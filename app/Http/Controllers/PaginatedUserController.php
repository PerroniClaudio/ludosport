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
            ->where('users.is_disabled', false)
            ->when($selectedRole, function ($query) use ($selectedRole) {
                return $query->whereHas('roles', function ($q) use ($selectedRole) {
                    $q->where('label', $selectedRole);
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
                $academy_id = $authUser->getActiveInstitutionId() ?? null;
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
                $school_id = $authUser->getActiveInstitutionId() ?? null;

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
            'manager' => ['id', 'name', 'email', 'academy', 'created_at'],
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
                        $query->leftJoin('academies_athletes', function ($join) {
                                $join->on('users.id', '=', 'academies_athletes.user_id')
                                    ->where('academies_athletes.is_primary', '=', true);
                            })
                            ->leftJoin('academies', function ($join) {
                                $join->on('academies_athletes.academy_id', '=', 'academies.id');
                            })
                            ->leftJoin('nations', function ($join) {
                                $join->on('academies.nation_id', '=', 'nations.id');
                            })
                            ->orderBy('nations.name', $sortDirection)
                            ->select('users.*');
                        break;
                    case 'academy':
                        // La query ordina per nome accademia primaria atleta (che è una sola per ogni utente)
                        $query->leftJoin('academies_athletes', function ($join) {
                                $join->on('users.id', '=', 'academies_athletes.user_id')
                                    ->where('academies_athletes.is_primary', '=', true);
                            })
                            ->leftJoin('academies', function ($join) {
                                $join->on('academies_athletes.academy_id', '=', 'academies.id')
                                    ->where('academies.is_disabled', '=', false);
                            })
                            ->orderByRaw('LOWER(TRIM(academies.name)) ' . $sortDirection)
                            ->select('users.*');
                    case 'school':
                        // Per evitare la perdita di dati, seleziona esplicitamente i campi della tabella users e aggiungi quelli delle join
                        // $query->leftJoin('schools_athletes', 'users.id', '=', 'schools_athletes.user_id')
                        //     ->leftJoin('schools', function ($join) {
                        //         $join->on('schools_athletes.school_id', '=', 'schools.id')
                        //             ->where('schools_athletes.is_primary', '=', true);
                        //     })
                        $query->leftJoin('schools_athletes', function ($join) {
                                $join->on('users.id', '=', 'schools_athletes.user_id')
                                    ->whereRaw('schools_athletes.id = (
                                        SELECT id FROM schools_athletes sa
                                        WHERE sa.user_id = users.id
                                        ORDER BY sa.is_primary DESC, sa.id ASC
                                        LIMIT 1
                                    )');
                            })
                            ->leftJoin('schools', function ($join) {
                                $join->on('schools_athletes.school_id', '=', 'schools.id');
                            })
                            ->orderBy('schools.name', $sortDirection)
                            ->select('users.*'); // <-- aggiungi questa riga per mantenere tutti i dati dell'utente
                        break;
                    case 'name':
                    case 'surname':
                    case 'email':
                        $query->orderByRaw("LOWER(TRIM($sortBy)) $sortDirection")
                        ->select('users.*');
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
                } else if (in_array($sortBy, ['name', 'surname', 'email'])) {
                    // Make sorting case-insensitive for string columns
                    $query->orderByRaw("LOWER(TRIM($sortBy)) $sortDirection");
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
                } else if (in_array($sortBy, ['name', 'surname', 'email'])) {
                    // Make sorting case-insensitive for string columns
                    $query->orderByRaw("LOWER(TRIM($sortBy)) $sortDirection");
                } else {
                    $query->orderBy($sortBy, $sortDirection);
                }

                $users = $query->paginate(10);
                break;

            case 'rector':
            case 'manager':
                $query = $baseQuery
                    ->select('*')
                    ->with(['academies']);

                // Handle special sorting for rectors
                if ($sortBy === 'academy') {
                    $query->leftJoin('academies_personnel', function ($join) {
                        $join->on('users.id', '=', 'academies_personnel.user_id')
                             ->where('academies_personnel.is_primary', '=', true);
                    })
                    ->leftJoin('academies', function ($join) {
                        $join->on('academies_personnel.academy_id', '=', 'academies.id');
                    })
                    ->orderByRaw("LOWER(TRIM(academies.name)) $sortDirection")
                    ->select('users.*');
                } else if (in_array($sortBy, ['name', 'surname', 'email'])) {
                    // Make sorting case-insensitive for string columns
                    $query->orderByRaw("LOWER(TRIM($sortBy)) $sortDirection");
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
                    $query->leftJoin('schools_personnel', function ($join) {
                        $join->on('users.id', '=', 'schools_personnel.user_id')
                             ->where('schools_personnel.is_primary', '=', true);
                    })
                    ->leftJoin('schools', function ($join) {
                        $join->on('schools_personnel.school_id', '=', 'schools.id');
                    })
                    ->orderByRaw("LOWER(TRIM(schools.name)) $sortDirection")
                    ->select('users.*');
                } else if (in_array($sortBy, ['name', 'surname', 'email'])) {
                    // Make sorting case-insensitive for string columns
                    $query->orderByRaw("LOWER(TRIM($sortBy)) $sortDirection");
                } else {
                    $query->orderBy($sortBy, $sortDirection);
                }

                $users = $query->paginate(10);
                break;

            case 'admin':
            default:
                // Make sorting case-insensitive for string columns
                if (in_array($sortBy, ['name', 'surname', 'email'])) {
                    $users = $baseQuery
                        ->select('*')
                        ->with(['roles'])
                        ->orderByRaw("LOWER(TRIM($sortBy)) $sortDirection")
                        ->paginate(10);
                } else {
                    $users = $baseQuery
                        ->select('*')
                        ->with(['roles'])
                        ->orderBy($sortBy, $sortDirection)
                        ->paginate(10);
                }
                break;
        }

        // Format additional data based on selected role
        foreach ($users as $user) {
            switch ($selectedRole) {
                case 'athlete':
                    // Usa la nazione dell'accademia primaria atleta se esiste, altrimenti quella dell'utente
                    $primaryAcademyAthlete = $user->primaryAcademyAthlete();
                    if ($primaryAcademyAthlete && $primaryAcademyAthlete->nation) {
                        $user->nation = $primaryAcademyAthlete->nation->name;
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
                case 'manager':
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
            'authUserRole' => $authUserRole,
        ]);
    }

    // Recupera gli utenti attivi senza un corso. Active users sarebbero quelli non disabilitati. 
    public function usersFilteredByActiveAndCoursePagination(Request $request) {
        $authUser = User::find(Auth::user()->id);
        $authUserRole = $authUser->getRole();

        if (!in_array($authUserRole, ['admin', 'rector', 'dean', 'manager'])) {
            return redirect()->route("dashboard")->with('error', 'You do not have the required role to access this page!');
        }

        // Handle sorting
        $sortBy = $request->get('sortedby', 'created_at');
        $sortDirection = $request->get('direction', 'asc');

        // Ensure valid direction
        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'asc';
        }

        // Validate sort field
        if (!in_array($sortBy, ['id', 'name', 'surname', 'email', 'subscription_year', 'nation', 'academy', 'school', 'has_paid_fee', 'created_at'])) {
            $sortBy = 'created_at';
        }
        
        $baseQuery = User::query()->where('users.is_disabled', false)->whereHas('roles', function ($query) {
                $query->where('label', 'athlete');
            });

        // Filtro attivo inattivo
        switch ($request->get('active', 'active')) {
            case 'active':
                $baseQuery->where('users.has_paid_fee', true);
                break;
            case 'inactive':
                $baseQuery->where('users.has_paid_fee', false);
                break;
            default:
                return redirect()->route("dashboard")->with('error', 'Invalid filter option!');
        }

        // Filtro con/senza corsi
        switch ($request->get('clans', 'without')) {
            case 'with':
                $baseQuery->whereHas('clans');
                break;
            case 'without':
                $baseQuery->whereDoesntHave('clans');
                break;
            default:
                return redirect()->route("dashboard")->with('error', 'Invalid filter option!');
        }

        // Filtro per ruolo del richiedente
        switch ($authUserRole) {
            case 'admin':
                break;
            case 'manager':
            case 'rector':
                // Utenti di una determinata accademia
                $academy_id = $authUser->getActiveInstitutionId() ?? null;
                if (!$academy_id) {
                    return redirect()->route("dashboard")->with('error', 'You don\'t have an academy assigned or didn\'t select one!');
                }

                $baseQuery->where(function ($query) use ($academy_id) {
                    $query->whereHas('academyAthletes', function ($q) use ($academy_id) {
                        $q->where('academy_id', $academy_id);
                    });
                });
                break;

            case 'dean':
                // Utenti di una determinata scuola
                $school_id = $authUser->getActiveInstitutionId() ?? null;

                if (!$school_id) {
                    return redirect()->route("dashboard")->with('error', 'You don\'t have a school assigned or didn\'t select one!');
                }

                $baseQuery->where(function ($query) use ($school_id) {
                    $query->whereHas('schoolAthletes', function ($q) use ($school_id) {
                        $q->where('school_id', $school_id);
                    });
                });
                break;

            default:
                return redirect()->route("dashboard")->with('error', 'You are not authorized to access this page!');
        }
        
        // Apply sorting
        switch ($sortBy){
            case 'name':
            case 'surname':
            case 'email':
                $baseQuery->orderByRaw("LOWER(TRIM($sortBy)) $sortDirection");
                break;
            case 'nation':
                $baseQuery->leftJoin('academies_athletes', function ($join) {
                        $join->on('users.id', '=', 'academies_athletes.user_id')
                            ->where('academies_athletes.is_primary', '=', true);
                    })
                    ->leftJoin('academies', function ($join) {
                        $join->on('academies_athletes.academy_id', '=', 'academies.id');
                    })
                    ->leftJoin('nations', function ($join) {
                        $join->on('academies.nation_id', '=', 'nations.id');
                    })
                    ->orderBy('nations.name', $sortDirection);
                break;
            case 'academy':
                $baseQuery->leftJoin('academies_athletes', function ($join) {
                        $join->on('users.id', '=', 'academies_athletes.user_id')
                            ->where('academies_athletes.is_primary', '=', true);
                    })
                    ->leftJoin('academies', function ($join) {
                        $join->on('academies_athletes.academy_id', '=', 'academies.id');
                    })
                    ->orderByRaw('LOWER(TRIM(academies.name)) ' . $sortDirection);
                break;
            case 'school':
                $baseQuery->leftJoin('schools_athletes', function ($join) {
                        $join->on('users.id', '=', 'schools_athletes.user_id')
                            ->whereRaw('schools_athletes.id = (
                                SELECT id FROM schools_athletes sa
                                WHERE sa.user_id = users.id
                                ORDER BY sa.is_primary DESC, sa.id ASC
                                LIMIT 1
                            )');
                    })
                    ->leftJoin('schools', function ($join) {
                        $join->on('schools_athletes.school_id', '=', 'schools.id');
                    })
                    ->orderBy('schools.name', $sortDirection);
                break;
            default:
                $baseQuery->orderBy($sortBy, $sortDirection);
                break;
        } 

        $users = $baseQuery->select('users.*')->paginate(10);

        // Format additional data
        foreach ($users as $user) {
            $user->primary_academy = $user->primaryAcademy() ? $user->primaryAcademy()->name : "No academy";
            $user->primary_school = $user->primarySchool() ? $user->primarySchool()->name : "No school";
            $user->nation = $user->primaryAcademyAthlete() ? $user->primaryAcademyAthlete()->nation->name : $user->nation->name ?? "Not set";
        }

        return view('users.filtered_by_active_and_course', [
            'users' => $users,
            'currentSort' => $sortBy,
            'currentDirection' => $sortDirection,
            'authUserRole' => $authUserRole,
        ]);
        
    }
}
