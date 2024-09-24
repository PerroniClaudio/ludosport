<?php

namespace App\Exports;

use App\Models\School;
use Maatwebsite\Excel\Concerns\FromArray;

class UsersSchoolExport implements FromArray {

    private $export;

    public function __construct($export) {
        $this->export = $export;
    }

    public function array(): array {


        $filters = json_decode($this->export->filters);

        $schools = [];

        foreach ($filters->schools as $school) {
            $schools[] = $school->id;
        }

        if ($filters->users_type == "athletes") {

            $users = School::whereIn('id', $schools)->with('athletes')->get()->flatMap(function ($school) {
                return $school->athletes->map(function ($user) use ($school) {
                    return [
                        $school->name,
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

            $users = School::whereIn('id', $schools)->with('personnel')->get()->flatMap(function ($school) {
                return $school->personnel->map(function ($user) use ($school) {
                    return [
                        $school->name,
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

            $users = School::whereIn('id', $schools)->with('athletes')->get()->flatMap(function ($school) {
                return $school->athletes->map(function ($user) use ($school) {
                    return [
                        $school->name,
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

            $personnel = School::whereIn('id', $schools)->with('personnel')->get()->flatMap(function ($school) {
                return $school->personnel->map(function ($user) use ($school) {
                    return [
                        $school->name,
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
                "School",
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
