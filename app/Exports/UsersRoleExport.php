<?php
namespace App\Exports;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\FromArray;

class UsersRoleExport implements FromArray {

    private $export;

    public function __construct($export) {
        $this->export = $export;
    }

    public function array(): array {

        $selected_roles = json_decode($this->export->filters)->selected_roles;
        $users = User::whereHas('roles', function ($query) use ($selected_roles) {
            $query->whereIn('role_id', $selected_roles);
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
