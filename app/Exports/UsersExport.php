<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromArray;

class UsersExport implements FromArray {

    private $export;

    public function __construct($export) {
        $this->export = $export;
    }

    public function array(): array {
        //

        $filters = json_decode($this->export->filters);
        $users = User::where('created_at', '>=', $filters->start_date)->where('created_at', '<=', $filters->end_date)->with('roles')->get()->map(function ($user) {
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

        $headers = [
            "Code",
            "Name",
            "Surname",
            "Email",
            "Roles",
            "Created At",
            "Updated At",
            "How found us"
        ];

        return [
            $headers,
            $users
        ];
    }
}
