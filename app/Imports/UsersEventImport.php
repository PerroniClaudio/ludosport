<?php

namespace App\Imports;

use App\Models\Event;
use App\Models\EventInstructorResult;
use App\Models\EventResult;
use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class UsersEventImport implements ToCollection {

    private $event_id = null;
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

            $user = User::where('email', $row[1])->first();
            $event = Event::find($row[0]);
            
            if(!$event) {
                $this->log[] = "['Event not found. email: " . $row[1] . " - event ID: " . $row[0] . "']";
                $this->is_partial = true;
                continue;
            }

            if(!$event->isFree() && !User::find($this->importingUser->id)->hasRole('admin')) {
                $this->log[] = "['Unauthorized to import user to paid event. email: " . $row[1] . " - event ID: " . $row[0] . "']";
                $this->is_partial = true;
                continue;
            }

            if ($user && $event) {
                if($event->resultType() == 'enabling') {
                    $weaponForm = $event->weaponForm();
                    if($event->max_participants > 0 && (($event->instructorResults()->count() + $event->waitingList->count()) >= $event->max_participants)){
                        $this->log[] = "['Event is full. Cannot add participant. email: " . $row[1] . " - event ID: " . $row[0] . "']";
                        $this->is_partial = true;
                        continue;
                    }
                    if (!EventInstructorResult::where('event_id', $row[0])->where('user_id', $user->id)->exists()) {
                        $eventInstructorResult = EventInstructorResult::create([
                            'event_id' => $row[0],
                            'user_id' => $user->id,
                            'weapon_form_id' => $weaponForm->id ?? null,
                        ]);
                    }
                } else if ($event->resultType() == 'ranking') {
                    if($event->max_participants > 0 && (($event->results()->count() + $event->waitingList->count()) >= $event->max_participants)){
                        $this->log[] = "['Event is full. Cannot add participant. email: " . $row[1] . " - event ID: " . $row[0] . "']";
                        $this->is_partial = true;
                        continue;
                    }
                    if(!EventResult::where('event_id', $row[0])->where('user_id', $user->id)->exists()) {
                        $eventResult = EventResult::create([
                            'event_id' => $row[0],
                            'user_id' => $user->id,
                            'war_points' => 0,
                            'style_points' => 0,
                            'total_points' => 0,
                        ]);
                    }
                }
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
