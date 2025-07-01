<?php

namespace App\Exports;

use App\Models\Clan;
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
// Così va bene. va fatto anche per personnel e l'altro
        if ($filters->users_type == "athletes") { 
            $users = [];
            $users = Clan::whereIn('id', $courses)->with('users')->get()->flatMap(function ($course) {
                return $course->users->map(function ($user) use ($course) {
                    return [
                        $course->name,
                        $user->unique_code,
                        $user->name,
                        $user->surname,
                        $user->email,
                        $user->roles->pluck('name')->implode(', '),
                        $user->created_at,
                        $user->updated_at,
                        $user->how_found_us ?? ""
                    ];
                });
            })->toArray();
        } else if ($filters->users_type == "personnel") {
            $users = Clan::whereIn('id', $courses)->with('personnel')->get()->flatMap(function ($course) {
                return $course->personnel->map(function ($user) use ($course){
                    return [
                        $course->name,
                        $user->unique_code,
                        $user->name,
                        $user->surname,
                        $user->email,
                        $user->roles->pluck('name')->implode(', '),
                        $user->created_at,
                        $user->updated_at,
                        $user->how_found_us ?? ""
                    ];
                });
            })->toArray();
        } else {

            $users = Clan::whereIn('id', $courses)->with('users')->get()->flatMap(function ($course) {
                return $course->users->map(function ($user) use ($course) {
                    return [
                        $course->name,
                        $user->unique_code,
                        $user->name,
                        $user->surname,
                        $user->email,
                        $user->roles->pluck('name')->implode(', '),
                        $user->created_at,
                        $user->updated_at,
                        $user->how_found_us ?? ""
                    ];
                });
            })->toArray();

            $personnel = Clan::whereIn('id', $courses)->with('personnel')->get()->flatMap(function ($course) {
                return $course->personnel->map(function ($user) use ($course){
                    return [
                        $course->name,
                        $user->unique_code,
                        $user->name,
                        $user->surname,
                        $user->email,
                        $user->roles->pluck('name')->implode(', '),
                        $user->created_at,
                        $user->updated_at,
                        $user->how_found_us ?? ""
                    ];
                });
            })->toArray();

            $users = array_merge($users, $personnel);
        }


        return [
            [
                "Clan",
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
