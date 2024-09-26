<?php

namespace App\Imports;

use App\Models\Event;
use App\Models\EventInstructorResult;
use App\Models\User;
use App\Models\WeaponForm;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class EventInstructorImport implements ToCollection {

    private $event = null;
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
        // protected $fillable = [
        //     'event_id',
        //     'user_id',
        //     'result',
        //     'notes',
        //     'stage',
        //     'weapon_form_id',
        // ];
        // result: ['passed', 'review', 'failed'] default: null;
        // stage: ['registered', 'pending', 'confirmed'] default: 'registered';
        // notes: max 100 chars;

        // Valori per ogni riga: 
        // ID evento - email utente - ID forma d'arma - risultato - note
        // Il risultato può essere 'passed', 'review', 'failed'
        // Lo stage non fa parte del documento e si lascia quello di default

        $firstRow = true;
        foreach ($collection as $row) {
            if ($firstRow) {
                $firstRow = false;
                continue;
            }

            if ($this->event == null) {
                $this->event = Event::find($row[0]);
            } else if ($this->event->id != $row[0]) {
                $this->event = Event::find($row[0]);
            }

            if(!$this->event || $this->event->resultType() != 'enabling') {
                continue;
            }

            $user = User::where('email', $row[1])->first();

            if(!$user) {
                continue;
            }

            $participation = EventInstructorResult::where('event_id', $this->event->id)->where('user_id', $user->id)->first();
            
            if (!$participation) {
                $this->is_partial = true;
                $this->log[] = "Error: User not registered for this event. User: " . $user->email . " - Event ID: " . $row[0];
                continue;
            }

            if($participation->stage == 'confirmed') {
                $this->is_partial = true;
                $this->log[] = "Error: Result already confirmed. User: " . $user->email . " - Event ID: " . $row[0];
                continue;
            }

            if($participation->stage == 'confirmed') {
                $this->is_partial = true;
                $this->log[] = "Error: Result already confirmed. User: " . $user->email . " - Event ID: " . $row[0];
                continue;
            }
            
            // Aggiorna i valori e poi salva
            $weaponForm = WeaponForm::find($row[2]) ?? null;
            $result = $row[3] ?? null;
            $notes = $row[4] ?? null;

            $eventWeaponForm = $this->event->weaponForm;

            $participationWeaponForm = $weaponForm ?? ($eventWeaponForm ?? null);

            if (in_array($participation->stage, ['registered', 'pending'])) {
                $participation->weapon_form_id = $participationWeaponForm->id ?? null;
                $participation->result = $result;
                $participation->notes = $notes;

                // Se il risultato è 'passed' o 'pending' allora deve essere revisionato dagli admin. altrimenti si può confermare.
                if ($result == 'failed') {
                    $participation->stage = 'confirmed';
                } else {
                    $participation->stage = 'pending';

                    // Se il risultato è 'passed' o 'review' si dà comunque la forma da atleta. Per quella da istruttore decidono gli admin.
                    if (in_array($result, ['passed', 'review']) && ($participationWeaponForm && !$participationWeaponForm->users()->where('user_id', $user->id)->exists())) {
                        // Il ruolo da atleta non va aggiunto se non c'è già
                        $participationWeaponForm->users()->attach($user->id);
                    }
                }

                $participation->save();
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
