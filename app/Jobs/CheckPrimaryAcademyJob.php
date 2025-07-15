<?php

namespace App\Jobs;

use App\Models\Academy;
use App\Models\School;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckPrimaryAcademyJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     * Questo job deve controllare che ogni utente abbia un'accademia primaria.
     * Se un utente ha accademie ma nessuna è primaria, imposta la prima accademia come primaria.
     * Se un utente non ha accademie, lo associa all'accademia "no-academy" e la imposta come primaria.
     */
    public function handle(): void
    {
        Log::info('Job started - CheckPrimaryAcademyJob');
        // Prende tutti gli utenti che hanno accademie associate (da atleta), escluso quelli che ne hanno solo una ed è già primaria (quindi sono già a posto).
        $users = User::whereHas('academyAthletes', function ($query) {
            $query->where('is_disabled', false)
                ->where(function ($q) {
                    $q->where('is_primary', false)
                        ->orWhereRaw('(SELECT COUNT(*) FROM academies_athletes WHERE user_id = users.id AND is_disabled = 0) != 1');
                });
        })
        ->with([
            'academyAthletes' => function ($query) {
                $query->where('is_disabled', false);
            },
            'schoolAthletes',
            'clans'
        ])
        ->get();
        
        if($users->isEmpty()) {
            Log::info('No users found for CheckPrimaryAcademyJob');
            return;
        } else {
            Log::info('Found ' . $users->count() . ' users for CheckPrimaryAcademyJob');
        }

        $athletesWithNoActiveAcademies = [];
        $othersWithNoActiveAcademies = [];
        $usersWithSingleAcademyNotPrimary = [];
        $usersWithMultipleAcademies = [];

        $noAcademy = Academy::where('slug', 'no-academy')->first();
        $noSchool = School::where('slug', 'no-school')->first();

        foreach ($users as $user) {
            $activeAcademies = $user->academyAthletes;

            // Se non ci sono accademie, controlla se ha il ruolo da atleta, associa a no-academy e la imposta come primaria
            if ($activeAcademies->isEmpty()) {
                if(!$user->hasRole('athlete')) {
                    $othersWithNoActiveAcademies[] = $user->id; // Per il log
                    continue;
                }
                $athletesWithNoActiveAcademies[] = $user->id; // Per il log

                if ($noAcademy) {
                    $user->academyAthletes()->syncWithoutDetaching([
                        $noAcademy->id => ['is_primary' => true]
                    ]);
                    $user->schoolAthletes()->syncWithoutDetaching([
                        $noSchool->id => ['is_primary' => true]
                    ]);
                    Log::channel('academy')->info('Athlete associated with academy', [
                        'user_id' => $user->id,
                        'academy_id' => $noAcademy->id,
                        'made_by' => 'Job - CheckPrimaryAcademyJob',
                    ]);
                }
                continue;
            }

            // Se c'è solo una accademia associata
            if ($activeAcademies->count() == 1) {
                $singleAcademy = $activeAcademies->first();
                // Se non è primary, impostala come primary
                if (!$singleAcademy->pivot->is_primary) {
                    $usersWithSingleAcademyNotPrimary[] = $user->id; // Per il log

                    DB::table('academies_athletes')
                        ->where('user_id', $user->id)
                        ->where('academy_id', $singleAcademy->id)
                        ->update(['is_primary' => true]);
                    Log::channel('academy')->info('Association academy-athlete set to primary', [
                        'user_id' => $user->id,
                        'academy_id' => $singleAcademy->id,
                        'made_by' => 'Job - CheckPrimaryAcademyJob',
                    ]);

                    // Se l'accademia è no-academy, associa anche la scuola no-school come primary
                    if($singleAcademy->id == $noAcademy->id){
                        $user->schoolAthletes()->syncWithoutDetaching([
                            $noSchool->id => ['is_primary' => true]
                        ]);
                    }
                }
                continue;
            }

            $usersWithMultipleAcademies[] = $user->id; // Per il log

            // Se ci sono più accademie e tra quelle c'è no-academy, rimuovila, insieme alla sue scuole e corsi (dovrebbe esserci solo no-school, senza corsi)
            if ($noAcademy && $activeAcademies->contains('id', $noAcademy->id)) {
                $removedCourses = $user->clans()->whereIn('school_id', $noAcademy->schools->pluck('id'))->get();
                foreach ($removedCourses as $course) {
                    $user->clans()->detach($course->id);
                }
                $removedCoursesIds = $removedCourses->pluck('id')->toArray();

                $removedSchools = $user->schoolAthletes()->where('academy_id', '==', $noAcademy->id ?? null)->get();
                foreach ($removedSchools as $school) {
                    $user->schoolAthletes()->detach($school->id);
                }
                $removedSchoolsIds = $removedSchools->pluck('id')->toArray();

                $user->academyAthletes()->detach($noAcademy->id);
                $removedAcademiesIds = [$noAcademy->id];
                // Metto tutti i dati su tutti e tre i canali. Si può modificare in futuro
                Log::channel('user')->info('Removed athlete associations', [
                    'made_by' => 'Job - CheckPrimaryAcademyJob',
                    'athlete' => $user->id,
                    'academies' => $removedAcademiesIds,
                    'schools' => $removedSchoolsIds,
                    'courses' => $removedCoursesIds,
                ]);
            }

            // Ridefinisci le accademie associate dopo la rimozione di no-academy
            $activeAcademies = $user->academyAthletes;

            // Se c'è una accademia
            if ($activeAcademies->count() == 1) {
                $singleAcademy = $activeAcademies->first();
                // Se non è primary, impostala come primary
                if (!$singleAcademy->pivot->is_primary) {
                    DB::table('academies_athletes')
                        ->where('user_id', $user->id)
                        ->where('academy_id', $singleAcademy->id)
                        ->update(['is_primary' => true]);
                    Log::channel('academy')->info('Association academy-athlete set to primary', [
                        'user_id' => $user->id,
                        'academy_id' => $singleAcademy->id,
                        'made_by' => 'Job - CheckPrimaryAcademyJob',
                    ]);
                }
                continue;
            }

            // Se ci sono più accademie associate, rimuovi quelle in eccesso usando il metodo del modello
            $primaryAcademy = $activeAcademies->first(function ($academy) {
                return $academy->pivot->is_primary;
            });

            // Logica per scegliere quale accademia preservare se non c'è una primaria
            $academyToPreserve = $primaryAcademy ?: null;
            if (!$academyToPreserve) {
                // Cerca accademia dove l'utente ha almeno una scuola con almeno un corso associato
                $academyWithCourses = $activeAcademies->first(function ($academy) use ($user) {
                    foreach ($academy->schools as $school) {
                        // Clan = corso
                        $hasCourse = $school->clan()->whereHas('users', function ($q) use ($user) {
                            $q->where('user_id', $user->id);
                        })->exists();
                        if ($hasCourse) {
                            return true;
                        }
                    }
                    return false;
                });

                if ($academyWithCourses) {
                    $academyToPreserve = $academyWithCourses;
                } else {
                    // Cerca accademia dove l'utente è associato almeno a una scuola
                    $academyWithSchool = $activeAcademies->first(function ($academy) use ($user) {
                        foreach ($academy->schools as $school) {
                            if ($school->athletes()->where('user_id', $user->id)->exists()) {
                                return true;
                            }
                        }
                        return false;
                    });

                    if ($academyWithSchool) {
                        $academyToPreserve = $academyWithSchool;
                    } else {
                        // Altrimenti preserva la prima accademia trovata
                        $academyToPreserve = $activeAcademies->first();
                    }
                }
            }

            // Se non c'è una primaria, la logica di rimozione è gestita dal metodo
            $user->removeAcademiesAthleteAssociations($academyToPreserve, null, 'CheckPrimaryAcademyJob');

            // Dopo la rimozione, assicurati che l'unica rimasta sia primary
            $activeAcademies = $user->academyAthletes;
            if ($activeAcademies->count() == 1) {
                $singleAcademy = $activeAcademies->first();
                if (!$singleAcademy->pivot->is_primary) {
                    DB::table('academies_athletes')
                        ->where('user_id', $user->id)
                        ->where('academy_id', $singleAcademy->id)
                        ->update(['is_primary' => true]);
                    Log::channel('academy')->info('Association academy-athlete set to primary', [
                        'user_id' => $user->id,
                        'academy_id' => $singleAcademy->id,
                        'made_by' => 'Job - CheckPrimaryAcademyJob',
                    ]);
                }
            }
        }

        Log::info('Job completed - CheckPrimaryAcademyJob', [
            'athletes_with_no_active_academies' => $athletesWithNoActiveAcademies,
            'others_with_no_active_academies' => $othersWithNoActiveAcademies,
            'users_with_single_academy_not_primary' => $usersWithSingleAcademyNotPrimary,
            'users_with_multiple_academies' => $usersWithMultipleAcademies,
        ]);
    }
}
