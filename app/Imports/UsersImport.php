<?php

namespace App\Imports;

use App\Models\Academy;
use App\Models\Nation;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Str;

class UsersImport implements ToCollection {

    public function collection(Collection $rows) {

        $firstRow = true;
        foreach ($rows as $row) {
            if ($firstRow) {
                $firstRow = false;
                continue;
            }

            $nationality = Nation::where('name', $row[3])->first();

            if ($row[4] == null) {
                $row[4] = 1;
            }
            $academy = Academy::where('id', $row[4])->first();
            if (!User::where('email', $row[2])->exists()) {
                $user = User::create([
                    'name'     => $row[0],
                    'surname'    => $row[1],
                    'email'    => $row[2],
                    'nation_id' => $nationality ? $nationality->id : 2,
                    'password' => Hash::make(Str::random(8)),
                    'academy_id' => $academy ? $academy->id : 1,
                    'subscription_year' => now()->year,
                ]);

                $user->roles()->attach(7);
                $user->academyAthletes()->attach($academy->id);
            }
        }
    }
}
