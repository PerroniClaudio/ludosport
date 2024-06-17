<?php

namespace App\Exports;

use App\Models\Clan;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromArray;

class UsersCourseExport implements FromArray {

    private $export;

    public function __construct($export) {
        $this->export = $export;
    }

    public function array(): array {

        $filters = json_decode($this->export->filters);

        $courses = [];

        foreach ($filters->courses as $course) {
            $courses[] = $course->id;
        }

        if ($filters->users_type == "athletes") {
            $users = Clan::whereIn('id', $courses)->with('users')->get()->map(function ($course) {
                return $course->users->map(function ($user) {
                    return [
                        $user->unique_code,
                        $user->name,
                        $user->surname,
                        $user->email,
                        $user->roles->map(function ($role) {
                            return $role->name;
                        })->implode(', '),
                        $user->created_at,
                        $user->updated_at
                    ];
                });
            })->toArray();
        } else if ($filters->users_type == "personnel") {
            $users = Clan::whereIn('id', $courses)->with('personnel')->get()->map(function ($course) {
                return $course->users->map(function ($user) {
                    return [
                        $user->unique_code,
                        $user->name,
                        $user->surname,
                        $user->email,
                        $user->roles->map(function ($role) {
                            return $role->name;
                        })->implode(', '),
                        $user->created_at,
                        $user->updated_at
                    ];
                });
            })->toArray();
        } else {

            $users = Clan::whereIn('id', $courses)->with('users')->get()->map(function ($course) {
                return $course->users->map(function ($user) {
                    return [
                        $user->unique_code,
                        $user->name,
                        $user->surname,
                        $user->email,
                        $user->roles->map(function ($role) {
                            return $role->name;
                        })->implode(', '),
                        $user->created_at,
                        $user->updated_at
                    ];
                });
            })->toArray();

            $personnel = Clan::whereIn('id', $courses)->with('personnel')->get()->map(function ($course) {
                return $course->users->map(function ($user) {
                    return [
                        $user->unique_code,
                        $user->name,
                        $user->surname,
                        $user->email,
                        $user->roles->map(function ($role) {
                            return $role->name;
                        })->implode(', '),
                        $user->created_at,
                        $user->updated_at
                    ];
                });
            })->toArray();

            $users = array_merge($users, $personnel);
        }


        return [
            [
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
