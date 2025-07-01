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

    public function __construct($user) {
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

            if(!$this->event) {
                $this->is_partial = true;
                $this->log[] = "Error: Event not found - User: " . $row[1] .  " - Event ID: " . $row[0];
                continue;
            }
            
            if($this->event->resultType() != 'enabling') {
                $this->is_partial = true;
                $this->log[] = "Error: Wrong event type - User: " . $row[1] .  " - Event ID: " . $row[0];
                continue;
            }
            
            $user = User::where('email', $row[1])->first();
            
            if(!$user) {
                $this->is_partial = true;
                $this->log[] = "Error: User not found - User: " . $row[1];
                continue;
            }
            
            $participation = EventInstructorResult::where('event_id', $this->event->id)->where('user_id', $user->id)->first();

            if (!$participation) {
                $this->is_partial = true;
                $this->log[] = "Error: User not registered for this event. User: " . $user->email . " - Event ID: " . $row[0];
                continue;
            }

            if ($participation->stage == 'confirmed') {
                $this->is_partial = true;
                $this->log[] = "Error: Result already confirmed. User: " . $user->email . " - Event ID: " . $row[0];
                continue;
            }

            if ($participation->stage == 'confirmed') {
                $this->is_partial = true;
                $this->log[] = "Error: Result already confirmed. User: " . $user->email . " - Event ID: " . $row[0];
                continue;
            }

            // Aggiorna i valori e poi salva
            $weaponForm = WeaponForm::find($row[2]) ?? null;
            $result = $row[3] ?? null;
            $notes = $row[7] ?? null;

            if(!$result || !in_array(strtolower($result), ['green', 'yellow', 'red'])) {
                $this->is_partial = true;
                $this->log[] = "Error: Result is missing or invalid. User: " . $user->email . " - Event ID: " . $row[0] . " - Result: " . $result;
                continue;
            }
            
            // Si traducono perchè loro vogliono usare i colori ma erano stati implementati passed, review e failed
            switch(strtolower($result)){
                case 'green':
                    $result = 'passed';
                    break;
                case 'yellow':
                    $result = 'review';
                    break;
                case 'red':
                    $result = 'failed';
                    break;
                default:
                    break;
            }

            // Aggiungere i campi alla tabella internship_duration(max 100 chars) internship_notes(max 100 chars) e retake(exam, course)
            // Aggiungere controlli in base al risultato per internship duration notes on the internship e retake exam / retake course. 
            // Aggiungere i valori al risultato
            // Aggiungere i valori alla tabeklla dei risultati visualizzata in admin e modificare nella tabella i risultati con green, yellow e red

            if($result == 'review') {
                $internshipDuration = $row[4] ?? null;
                $internshipNotes = $row[5] ?? null;

                if(!$internshipDuration) {
                    $this->is_partial = true;
                    $this->log[] = "Error: Internship duration is missing. User: " . $user->email . " - Event ID: " . $row[0];
                    continue;
                }

                if(!$internshipNotes) {
                    $this->is_partial = true;
                    $this->log[] = "Error: Internship notes are missing. User: " . $user->email . " - Event ID: " . $row[0];
                    continue;
                }

                $participation->internship_duration = $internshipDuration;
                $participation->internship_notes = $internshipNotes;
            } 
            
            if($result == 'failed') {
                $retake = $row[6] ?? null;

                if(!$retake) {
                    $this->is_partial = true;
                    $this->log[] = "Error: Retake is missing. User: " . $user->email . " - Event ID: " . $row[0];
                    continue;
                }

                if(!in_array(strtolower($retake), ['retake exam', 'retake course', 'exam', 'course'])) {
                    $this->is_partial = true;
                    $this->log[] = "Error: Retake is invalid. User: " . $user->email . " - Event ID: " . $row[0] . " - Retake: " . $retake;
                    continue;
                }

                switch(strtolower($retake)){
                    case 'exam':
                    case 'retake exam':
                        $retake = 'exam';
                        break;
                    case 'course':
                    case 'retake course':
                        $retake = 'course';
                        break;
                    default:
                        break;
                }

                $participation->retake = $retake;
            }
            
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
                        $participationWeaponForm->users()->syncWithoutDetaching($user->id);
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
    public function getEventId() {
        return $this->event->id;
    }
}
