<?php

namespace App\Imports;

use App\Models\Academy;
use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class UsersAcademyImport implements ToCollection {

    private $importingUser = null;
    private $log = [];
    private $is_partial = false;

    public function __construct($user)
    {
        $this->importingUser = $user;
    }

    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection) {

        $noAcademy = Academy::where('slug', 'no-academy')->first();

        $firstRow = true;
        foreach ($collection as $row) {
            if ($firstRow) {
                $firstRow = false;
                continue;
            }

            try {
                $user = User::where('email', $row[0])->first();
                $academy = Academy::where('id', $row[1])->first();

                if(!$user) {
                    $this->log[] = "['Error: User not found. Email: " . $row[0] . "']";
                    $this->is_partial = true;
                    continue;
                }

                if(!$academy) {
                    $this->log[] = "['Error: Academy not found. ID: " . $row[1] . "']";
                    $this->is_partial = true;
                    continue;
                }

                if(!$user->academyAthletes()->first()->id == $academy->id) {
                    // L'atleta può avere solo un'accademia associata
                    $user->removeAcademiesAthleteAssociations();
                    $user->academyAthletes()->syncWithoutDetaching([$academy->id]);
                }

                // Se l'atleta non ha l'accademia principale, la assegna
                if(!$user->primaryAcademyAthlete()){
                    $schoolAcademy = null;
                    if($user->primarySchoolAthlete()){
                        $schoolAcademy = $user->primarySchoolAthlete()->academy;
                    }
                    $user->setPrimaryAcademyAthlete($schoolAcademy ? $schoolAcademy->id : $academy->id);
                }
                
            } catch (\Exception $e) {
                $this->log[] = "['Error: User email: " . $row[0] . " - Academy ID: " . $row[1] . " - Error message: " . $e->getMessage() . "']";
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
