<?php

namespace App\Exports;

use App\Models\EventResult;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\FromArray;

class EventParticipantsExport implements FromArray {

    private $export;

    public function __construct($export) {
        $this->export = $export;
    }

    public function array(): array {

        $filters = collect(json_decode($this->export->filters)->filters);

        $eventsIds = $filters->pluck('id');
        
        $template_data = EventResult::whereIn('event_id', $eventsIds)->with(['user', 'event'])->get()->map(function ($event_result) {
            return [
                $event_result->event->name,
                $event_result->user->unique_code,
                $event_result->user->name,
                $event_result->user->surname,
                $event_result->user->email,
                $event_result->user->roles->pluck('name')->implode(', '),
                $event_result->user->created_at,
                $event_result->user->updated_at
            ];
        })->toArray();
        

        $headers = [
            "Event",
            "Code",
            "Name",
            "Surname",
            "Email",
            "Roles",
            "Created At",
            "Updated At"
        ];

        return [
            $headers,
            $template_data
        ];
    }
}
