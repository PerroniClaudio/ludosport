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
            if ($user && $event) {
                if($event->result_type == 'enabling') {
                    if (!EventInstructorResult::where('event_id', $row[0])->where('user_id', $user->id)->exists()) {
                        $eventInstructorResult = EventInstructorResult::create([
                            'event_id' => $row[0],
                            'user_id' => $user->id,
                        ]);
                    }
                } else if ($event->result_type == 'ranking') {
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
}
