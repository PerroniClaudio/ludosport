<?php

namespace App\Imports;

use App\Models\School;
use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class UsersSchoolImport implements ToCollection {
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
            $school = School::where('id', $row[1])->first();

            if ($user && $school) {
                $user->academyAthletes()->syncWithoutDetaching([$school->academy->id]);
                $user->schoolAthletes()->syncWithoutDetaching([$school->id]);
            }
        }
    }
}
