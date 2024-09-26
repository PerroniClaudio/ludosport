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

    private $importingUser = null;
    private $log = [];
    private $is_partial = false;

    public function __construct($user)
    {
        $this->importingUser = $user;
    }

    public function collection(Collection $rows) {

        $firstRow = true;
        foreach ($rows as $row) {
            if ($firstRow) {
                $firstRow = false;
                continue;
            }
            
            try {
                $nationality = Nation::where('name', $row[3])->first();

                $noAcademy = Academy::where('slug', 'no-academy')->first();
                
                if ($row[4] == null) {
                    if($noAcademy) {
                        $row[4] = $noAcademy->id;
                    } else {
                        $row[4] = 1;
                    }
                }
                $academy = Academy::where('id', $row[4])->first();
                
                if (User::where('email', $row[2])->exists()) {
                    $this->log[] = "['Error: User email already exists. Email: " . $row[2] . "']";
                    $this->is_partial = true;
                    continue;
                }
                $user = User::create([
                    'name'     => $row[0],
                    'surname'    => $row[1],
                    'email'    => $row[2],
                    'nation_id' => $nationality ? $nationality->id : 2,
                    'password' => Hash::make(Str::random(8)),
                    'academy_id' => $academy ? $academy->id : $noAcademy->id,
                    'subscription_year' => now()->year,
                ]);
                if(!$user){
                    $this->log[] = "['Error: User not created. Check for duplicated data or already existent user. User email: " . $row[2] . "']";
                    $this->is_partial = true;
                    continue;
                }
                $user->roles()->attach(7);
                $user->academyAthletes()->attach($academy ? $academy->id : $noAcademy->id);
            } catch (\Exception $e) {
                $this->log[] = "['Error: User email: " . $row[2] . " - Error message: " . $e->getMessage() . "']";
                $this->is_partial = true;
                continue;
            }
        }
    }

    public function getLogArray() {
        return $this->log;
    }
    public function getIsPartial() {
        return $this->is_partial;
    }
}
