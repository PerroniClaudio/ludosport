<?php

namespace App\Exports;

use App\Models\EventResult;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;

class EventsStyleExport implements WithMultipleSheets {

    use Exportable;

    private $export;

    public function __construct($export) {
        $this->export = $export;
    }

    public function sheets(): array {
        $sheets = [];

        $filters = collect(json_decode($this->export->filters)->filters);

        foreach ($filters as $event) {


            $users = EventResult::where('event_id', '=', $event->id)->with('user')->get()->map(function ($event_result) {
                return [
                    $event_result->user->unique_code,
                    $event_result->user->name,
                    $event_result->user->surname,
                    $event_result->style_points
                ];
            })->toArray();

            $sheets[] = new EventsStylePointsSheet($users, $event->id, $event->name);
        }

        return $sheets;
    }
}

class EventsStylePointsSheet implements FromArray, WithTitle {

    private $users;
    private $event_id;
    private $event_name;

    public function __construct($users, $event_id, $event_name) {
        $this->users = $users;
        $this->event_id = $event_id;
        $this->event_name = $event_name;
    }

    public function array(): array {
        return [
            [
                "Code",
                "Name",
                "Surname",
                "Style Points"
            ], $this->users
        ];
    }

    public function title(): string {
        return 'Event ' . $this->event_id . ' - ' . $this->event_name;
    }
}
