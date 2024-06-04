<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class EventWarImport implements ToCollection
{
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
                $this->event = Event::find($row[0])->first();
            } else if ($this->event->id != $row[0]) {
                $this->event = Event::find($row[0])->first();
            }

            $user = User::where('email', $row[1])->first();

            $pointsEarned = round((($usersCount - $userPosition) + 1) * $this->event->eventMultiplier(), 0, PHP_ROUND_HALF_UP);
            $participation = EventResult::where('event_id', $this->event->id)->where('user_id', $user->id)->first();

            $participation->war_points = $pointsEarned;

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
