<?php
namespace App\Exports;

use App\Models\School;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromArray;

class UsersRoleExport implements FromArray {

    private $export;

    public function __construct($export) {
        $this->export = $export;
    }

    public function array(): array {

        $selected_roles = json_decode($this->export->filters)->selected_roles;
        $requestingUser = User::find($this->export->user_id);
        $requestingRole = $this->export->userRole?->name;

        $users = User::whereHas('roles', function ($query) use ($selected_roles) {
            $query->whereIn('role_id', $selected_roles);
        })->when(in_array($requestingRole, ['rector', 'manager', 'dean']), function ($query) use ($requestingUser, $requestingRole) {
            $scopeId = match ($requestingRole) {
                'rector', 'manager' => $this->export->userAcademy?->id,
                'dean' => $this->export->userSchool?->id,
                default => null,
            };

            // Si ferma se scopeId è null
            if (!$scopeId) {
                $query->whereRaw('1 = 0');
                return;
            }

            // Restituisce questi dati se l'utente è un rettore o manager
            if (in_array($requestingRole, ['rector', 'manager'])) {
                $query->where(function ($academyQuery) use ($scopeId) {
                    $academyQuery->whereHas('academies', function ($q) use ($scopeId) {
                        $q->where('academy_id', $scopeId);
                    })->orWhereHas('academyAthletes', function ($q) use ($scopeId) {
                        $q->where('academy_id', $scopeId);
                    })->orWhereHas('schools', function ($q) use ($scopeId) {
                        $q->where('academy_id', $scopeId);
                    })->orWhereHas('schoolAthletes', function ($q) use ($scopeId) {
                        $q->where('academy_id', $scopeId);
                    });
                });
                return;
            }

            // Restituisce questi dati se l'utente è un preside
            $query->where(function ($schoolQuery) use ($scopeId) {
                $schoolQuery->whereHas('schools', function ($q) use ($scopeId) {
                    $q->where('school_id', $scopeId);
                })->orWhereHas('schoolAthletes', function ($q) use ($scopeId) {
                    $q->where('school_id', $scopeId);
                })->orWhereHas('clansPersonnel', function ($q) use ($scopeId) {
                    $q->where('school_id', $scopeId);
                })->orWhereHas('clans', function ($q) use ($scopeId) {
                    $q->where('school_id', $scopeId);
                });
            });
        })->with('roles')->get()->map(function ($user) {
            return [
                $user->unique_code,
                $user->name,
                $user->surname,
                $user->email,
                $user->roles->map(function ($role) {
                    return $role->name;
                })->implode(', '),
                $user->created_at,
                $user->updated_at,
                $user->how_found_us ?? ""
            ];
        })->toArray();

        return [
            [
                "Code",
                "Name",
                "Surname",
                "Email",
                "Roles",
                "Created At",
                "Updated At",
                "How found us"
            ],
            $users
        ];
    }
}
