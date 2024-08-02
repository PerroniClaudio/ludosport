<?php

namespace App\Imports;

use App\Models\Event;
use App\Models\EventResult;
use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class EventWarImport implements ToCollection
{
    private $event = null;
    
    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection)
    {
        //

        $usersCount = count($collection);
        $userPosition = 1;

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
                continue;
            }

            $user = User::where('email', $row[1])->first();

            if(!$user) {
                continue;
            }

            $pointsEarned = round((($usersCount - $userPosition) + 1) * $this->event->eventMultiplier(), 0, PHP_ROUND_HALF_UP);
            $participation = EventResult::where('event_id', $this->event->id)->where('user_id', $user->id)->first();

            if (!$participation) {
                continue;
            }

            $participation->war_points = $pointsEarned ?? 0;

            switch ($userPosition) {
                case 1:
                    $participation->bonus_war_points = $this->event->eventBonusPoints("FIRST_IN_WAR");
                    break;
                case 2:
                    $participation->bonus_war_points = $this->event->eventBonusPoints("SECOND_IN_WAR");
                    break;
                case 3:
                    $participation->bonus_war_points = $this->event->eventBonusPoints("THIRD_IN_WAR");
                    break;
                default:
                    $participation->bonus_war_points = 0;
                    break;
            }

            $participation->total_war_points = $participation->war_points + $participation->bonus_war_points;
            $participation->save();

            $userPosition++;
        }
    }
}