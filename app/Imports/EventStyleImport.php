<?php

namespace App\Imports;

use App\Models\Event;
use App\Models\EventResult;
use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class EventStyleImport implements ToCollection {

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
        //

        $usersCount = count($collection) - 1; // Exclude the header row

        // Check if the number of participants in the file matches the number of participants in the event
        if ($usersCount != EventResult::where('event_id', $collection[1][0])->count()) {
            $this->log[] = "['Number of participants in the file does not match the number of participants in the event. event ID: " . $collection[1][0] . "']";
            throw new \Exception('Number of participants in the file does not match the number of participants in the event.');
            return;
        }

        // Check if all rows belong to the same event and to real participants
        $this->event = Event::find($collection[1][0]);
        $eventParticipantsIds = EventResult::where('event_id', $collection[1][0])->pluck('user_id');
        $eventParticipantEmails = User::whereIn('id', $eventParticipantsIds)->pluck('email')->toArray();
        $eventId = $collection[1][0];
        $invalidEventIds = $collection->filter(function ($row, $index) use ($eventId, $eventParticipantEmails) {
            if ($index == 0) {
                return false;
            }
            return ($row[0] != $eventId || (!in_array($row[1], $eventParticipantEmails)));
        });
        if ($invalidEventIds->isNotEmpty()) {
            $this->log[] = "['Error: Event ID mismatch. All rows must belong to the same event (event id).']";
            throw new \Exception('Error: Event ID mismatch. All rows must belong to the same event (event id).');
            return;
        }

        // Check if all rows have a position value
        $invalidPositions = $collection->filter(function ($row, $index) {
            if ($index == 0) {
                return false;
            }
            return empty($row[2]);
        });
        if ($invalidPositions->isNotEmpty()) {
            $this->log[] = "['Error: One or more rows have missing position values.']";
            throw new \Exception('Error: One or more rows have missing position values.');
            return;
        }

        // Given that the data are correct, we should clear the previous style points and calculate the new ones
        EventResult::where('event_id', $this->event->id)->update([
            'style_points' => 0,
            'bonus_style_points' => 0,
            'total_style_points' => 0,
        ]);


        $firstRow = true;
        
        // foreach ($collection as $row) {
        //     if ($firstRow) {
        //         $firstRow = false;
        //         continue;
        //     }

        //     $userPosition = $row[2];

        //     if ($this->event == null) {
        //         $this->event = Event::find($row[0]);
        //     } else if ($this->event->id != $row[0]) {
        //         $this->event = Event::find($row[0]);
        //     }

        //     if (!$this->event || $this->event->resultType() != 'ranking') {
        //         continue;
        //     }

        //     $user = User::where('email', $row[1])->first();

        //     if (!$user) {
        //         continue;
        //     }

        //     $pointsEarned = round((($usersCount - $userPosition) + 1) * $this->event->eventMultiplier(), 0, PHP_ROUND_HALF_UP);
        //     $participation = EventResult::where('event_id', $this->event->id)->where('user_id', $user->id)->first();

        //     if (!$participation) {
        //         continue;
        //     }

        //     $participation->style_points = $pointsEarned ?? 0;

        //     switch ($userPosition) {
        //         case 1:
        //             $participation->bonus_style_points = $this->event->eventBonusPoints("FIRST_IN_STYLE");
        //             break;
        //         case 2:
        //             $participation->bonus_style_points = $this->event->eventBonusPoints("SECOND_IN_STYLE");
        //             break;
        //         case 3:
        //             $participation->bonus_style_points = $this->event->eventBonusPoints("THIRD_IN_STYLE");
        //             break;
        //         default:
        //             $participation->bonus_style_points = 0;
        //             break;
        //     }

        //     $participation->total_style_points = $participation->style_points + $participation->bonus_style_points;
        //     $participation->save();

        //     $userPosition++;
        // }
        

        $results = [];

        foreach ($collection as $row) {
            if ($firstRow) {
                $firstRow = false;
                continue;
            }
            
            $user = User::where('email', $row[1])->first();
            if (!$user) continue;

            $userPosition = $row[2];
            
            if (($this->event == null) || ($this->event->id != $row[0])) {
                $this->event = Event::find($row[0]);
            }

            if (!$this->event || ($this->event->resultType() != 'ranking')) continue;

            $pointsEarned = round((($usersCount - $userPosition) + 1) * $this->event->eventMultiplier(), 0, PHP_ROUND_HALF_UP) ?? 0;
            
            $participation = EventResult::where('event_id', $this->event->id)->where('user_id', $user->id)->first();
            if (!$participation) continue;

            // Calcola bonus
            $bonus = 0;
            switch ($userPosition) {
                case 1:
                    $bonus = $this->event->eventBonusPoints("FIRST_IN_STYLE");
                    break;
                case 2:
                   $bonus = $this->event->eventBonusPoints("SECOND_IN_STYLE");
                    break;
                case 3:
                   $bonus = $this->event->eventBonusPoints("THIRD_IN_STYLE");
                    break;
                default:
                    $participation->bonus_style_points = 0;
                    break;
            }

            $results[] = [
                'participation' => $participation,
                'style_points' => $pointsEarned,
                'bonus_style_points' => $bonus,
                'total_style_points' => $pointsEarned + $bonus,
            ];
        }

        // Ordina per total_style_points discendente, poi per nome e cognome ascendente
        usort($results, function ($a, $b) {
           if( $a['total_style_points'] === $b['total_style_points']) {
                return strcmp($a['participation']->user->name . ' ' . $a['participation']->user->surname, $b['participation']->user->name . ' ' . $b['participation']->user->surname);
            }
            return $b['total_style_points'] <=> $a['total_style_points'];
        });

        $partecipationPoints = 1; // Punti di partecipazione da assegnare a tutti i partecipanti (al momento fisso a 1)

        // Azzeramento dal 65° in poi
        foreach ($results as $index => $result) {
            if ($index >= 64) { // 0-based index, quindi 64 = 65esimo
                $result['participation']->style_points = 0;
                $result['participation']->bonus_style_points = 0;
                $result['participation']->total_style_points = $partecipationPoints;
            } else {
                $result['participation']->style_points = $result['style_points'];
                $result['participation']->bonus_style_points = $result['bonus_style_points'];
                $result['participation']->total_style_points = $result['total_style_points'] + $partecipationPoints;
            }
            $result['participation']->save();
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
