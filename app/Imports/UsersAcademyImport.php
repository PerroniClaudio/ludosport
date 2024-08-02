<?php

namespace App\Imports;

use App\Models\Academy;
use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class UsersAcademyImport implements ToCollection {
    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection) {


        $firstRow = true;
        foreach ($collection as $row) {
            if ($firstRow) {
                $firstRow = false;
                continue;
            }

            $user = User::where('email', $row[0])->first();
            $academy = Academy::where('id', $row[1])->first();

            if ($user && $academy && !$user->academyAthletes->contains($academy->id)) {
                $user->academyAthletes()->attach($academy->id);
                $user->save();
            }
        }
    }
}
