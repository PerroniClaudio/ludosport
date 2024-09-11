<?php

namespace App\Imports;

use App\Models\Clan;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class UsersCourseImport implements ToCollection {
    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection) {
        //

        $firstRow = true;
        foreach ($collection as $row) {
            if ($firstRow) {
                $firstRow = false;
                continue;
            }

            $user = User::where('email', $row[0])->first();
            $course = Clan::where('id', $row[1])->first();

            if ($user && $course) {
                $user->academyAthletes()->syncWithoutDetaching([$course->academy->id]);
                $user->schoolAthletes()->syncWithoutDetaching([$course->school->id]);
                $user->clans()->syncWithoutDetaching([$course->id]);
                $user->roles()->syncWithoutDetaching(Role::where('label', 'athlete')->first()->id);
            }
        }
    }
}
