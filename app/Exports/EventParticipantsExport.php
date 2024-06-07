<?php

namespace App\Exports;

use App\Models\EventResult;
use Maatwebsite\Excel\Concerns\FromArray;

class EventParticipantsExport  implements FromArray {


    private $event_id;

    public function __construct($event_id) {
        $this->event_id = $event_id;
    }

    public function array(): array {

        $headers = [
            "Code",
            "Name",
            "Surname"
        ];

        $template_data = EventResult::where('event_id', '=', $this->event_id)->with('user')->get()->map(function ($event_result) {
            return [
                $event_result->user->unique_code,
                $event_result->user->name,
                $event_result->user->surname
            ];
        })->toArray();

        return [
            $headers,
            $template_data
        ];
    }
}
