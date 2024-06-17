<?php

namespace App\Exports;

use App\Models\EventResult;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;


class EventsParticipantsExport implements WithMultipleSheets {

    use Exportable;

    private $export;

    public function __construct($export) {
        $this->export = $export;
    }

    public function sheets(): array {
        $sheets = [];

        $filters = json_decode($this->export->filters);

        $events = [];

        foreach ($filters->events as $event) {


            $users = EventResult::where('event_id', '=', $event->id)->with('user')->get()->map(function ($event_result) {
                return [
                    $event_result->user->unique_code,
                    $event_result->user->name,
                    $event_result->user->surname
                ];
            })->toArray();

            $sheets[] = new EventsParticipantsSheet($users, $event->id);
        }

        return $sheets;
    }
}

class EventsParticipantsSheet implements FromArray {

    private $users;
    private $event_id;

    public function __construct($users, $event_id) {
        $this->users = $users;
        $this->event_id = $event_id;
    }

    public function array(): array {
        return [
            [
                "Code",
                "Name",
                "Surname"
            ], $this->users
        ];
    }

    public function title(): string {
        return 'Event ' . $this->event_id;
    }
}
