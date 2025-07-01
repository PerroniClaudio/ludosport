<?php

namespace App\Exports;

use App\Models\EventInstructorResult;
use App\Models\EventResult;
use Maatwebsite\Excel\Concerns\FromArray;

class EventParticipantsExport implements FromArray {

    private $eventId;
    private $resultType;

    public function __construct($eventId, $resultType) {
        $this->eventId = $eventId;
        $this->resultType = $resultType;
    }

    public function array(): array {

        // $filters = collect(json_decode($this->export->filters)->filters);

        // $eventsIds = $filters->pluck('id');
        if($this->resultType == 'enabling'){
            $template_data = EventInstructorResult::where('event_id', $this->eventId)->with(['user', 'event'])->get()->map(function ($event_result) {
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
        } else {
            $template_data = EventResult::where('event_id', $this->eventId)->with(['user', 'event'])->get()->map(function ($event_result) {
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
        }
        

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
