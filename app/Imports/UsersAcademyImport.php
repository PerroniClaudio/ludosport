<?php

namespace App\Imports;

use App\Models\Academy;
use App\Models\School;
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

        $importingUserRole = $this->importingUser->getHighestRole();

        $firstRow = true;
        foreach ($collection as $row) {
            if ($firstRow) {
                $firstRow = false;
                continue;
            }

            try {
                $noSchool = School::where('slug', 'no-school')->first();

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

                if($user->academyAthletes()->first()->id != $academy->id) {
                    // L'admin può farlo sempre, il rettore solo se l'accademia è no academy, gli altri non hanno accesso alla funzionalità.
                    if($importingUserRole !== 'admin' && ($importingUserRole !== "rector" || $user->academyAthletes()->first()->id !== 1)) {
                        $this->log[] = "['Error: The " . $importingUserRole . " cannot import this user from another academy. Email: " . $row[0] . "']";
                        $this->is_partial = true;
                        continue;
                    }
                    // L'atleta può avere solo un'accademia associata
                    $user->removeAcademiesAthleteAssociations(null, $this->importingUser->id);
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

                if($user->schoolAthletes()->count() == 0) {
                    $user->schoolAthletes()->syncWithoutDetaching([$noSchool ? $noSchool->id : 1]);
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
