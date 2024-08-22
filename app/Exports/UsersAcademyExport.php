<?php

namespace App\Exports;

use App\Models\Academy;
use Maatwebsite\Excel\Concerns\FromArray;

class UsersAcademyExport implements FromArray {
    private $export;

    public function __construct($export) {
        $this->export = $export;
    }

    public function array(): array {
        //

        $filters = json_decode($this->export->filters);

        $academies = [];

        foreach ($filters->academies as $academy) {
            $academies[] = $academy->id;
        }

        if ($filters->users_type == "athletes") {
            $users = Academy::whereIn('id', $academies)->with('athletes')->get()->flatMap(function ($academy) {
                return $academy->athletes->map(function ($user) use ($academy) {
                    return [
                        $academy->name,
                        $user->unique_code,
                        $user->name,
                        $user->surname,
                        $user->email,
                        $user->roles->pluck('name')->implode(', '),
                        $user->created_at,
                        $user->updated_at
                    ];
                });
            })->toArray();
        } else if ($filters->users_type == "personnel") {
            $users = Academy::whereIn('id', $academies)->with('personnel')->get()->flatMap(function ($academy) {
                return $academy->personnel->map(function ($user) use ($academy) {
                    return [
                        $academy->name,
                        $user->unique_code,
                        $user->name,
                        $user->surname,
                        $user->email,
                        $user->roles->pluck('name')->implode(', '),
                        $user->created_at,
                        $user->updated_at
                    ];
                });
            })->toArray();
        } else {
            $users = Academy::whereIn('id', $academies)->with('users')->get()->flatMap(function ($academy) {
                return $academy->users->map(function ($user) use ($academy) {
                    return [
                        $academy->name,
                        $user->unique_code,
                        $user->name,
                        $user->surname,
                        $user->email,
                        $user->roles->pluck('name')->implode(', '),
                        $user->created_at,
                        $user->updated_at
                    ];
                });
            })->toArray();

            $personnel = Academy::whereIn('id', $academies)->with('personnel')->get()->flatMap(function ($academy) {
                return $academy->personnel->map(function ($user) use ($academy) {
                    return [
                        $academy->name,
                        $user->unique_code,
                        $user->name,
                        $user->surname,
                        $user->email,
                        $user->roles->pluck('name')->implode(', '),
                        $user->created_at,
                        $user->updated_at
                    ];
                });
            })->toArray();

            $users = array_merge($users, $personnel);
        }


        return [
            [
                "Academy",
                "Code",
                "Name",
                "Surname",
                "Email",
                "Roles",
                "Created At",
                "Updated At"
            ],
            $users
        ];
    }
}
