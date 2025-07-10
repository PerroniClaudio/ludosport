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

        $calculationBase = $usersCount > 64 ? 64 : $usersCount; // Base for calculation, max 64 even if there are more participants.
        $partecipationPoints = 1; // Punti di partecipazione da assegnare a tutti i partecipanti (al momento fisso a 1)


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

            $participation = EventResult::where('event_id', $this->event->id)->where('user_id', $user->id)->first();
            if (!$participation) continue;

            // Assegna i punti stile (style points) in base alla posizione nell'excel.
            $pointsEarned = $userPosition > 64 
                ? $partecipationPoints
                : $partecipationPoints + (round((($calculationBase - $userPosition) + 1) * $this->event->eventMultiplier(), 0, PHP_ROUND_HALF_UP) ?? 0);
            

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
                    break;
            }

            // Aggiorna i risultati aggiungendo i punti di partecipazione
            $participation->update([
                'style_points' => $pointsEarned,
                'bonus_style_points' => $bonus,
                'total_style_points' => $pointsEarned + $bonus,
            ]);
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
