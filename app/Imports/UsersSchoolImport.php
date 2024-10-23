<?php

namespace App\Imports;

use App\Models\Academy;
use App\Models\School;
use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class UsersSchoolImport implements ToCollection {

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
        //

        $firstRow = true;

        foreach ($collection as $row) {
            if ($firstRow) {
                $firstRow = false;
                continue;
            }

            try {
                $user = User::where('email', $row[0])->first();
                $school = School::where('id', $row[1])->first();

                if(!$user) {
                    $this->log[] = "['Error: User not found. Email: " . $row[0] . "']";
                    $this->is_partial = true;
                    continue;
                }

                if(!$school) {
                    $this->log[] = "['Error: School not found. ID: " . $row[1] . "']";
                    $this->is_partial = true;
                    continue;
                }

                if(!$user->schoolAthletes()->where('school_id', $school->id)->exists()) {
                    
                    if($user->academyAthletes()->first()->id != $school->academy->id) {
                        // L'atleta può avere solo un'accademia associata
                        $user->removeAcademiesAthleteAssociations();
                    }

                    $user->academyAthletes()->syncWithoutDetaching([$school->academy->id]);
                    $user->schoolAthletes()->syncWithoutDetaching([$school->id]);
                }
                
                $noAcademy = Academy::where('slug', 'no-academy')->first();
                if ($user->academyAthletes()->whereNot('academy_id', $noAcademy->id)->count() > 0) {
                    $noAcademy->athletes()->detach($user->id);
                }

                // Se l'atleta non ha l'accademia principale, la assegna
                if(!$user->primaryAcademyAthlete()){
                    $schoolAcademy = null;
                    if($user->primarySchoolAthlete()){
                        $schoolAcademy = $user->primarySchoolAthlete()->academy;
                    } 
                    $user->setPrimaryAcademyAthlete($schoolAcademy ? $schoolAcademy->id : $school->academy->id);
                }
                // Se l'atleta non ha la scuola principale, la assegna
                if(!$user->primarySchoolAthlete()){
                    $user->setPrimarySchoolAthlete($school->id);
                }
                
            } catch (\Exception $e) {
                $this->log[] = "['Error: User email: " . $row[0] . " - School ID: " . $row[1] . " - Error message: " . $e->getMessage() . "']";
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
