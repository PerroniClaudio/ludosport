<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;

class DocumentEventsExport implements FromArray
{
    public function __construct(
        private Collection $events,
        private array $eventLabels,
        private array $resultLabels,
    ) {}

    public function array(): array
    {
        return array_merge([[
            'Date',
            'User',
            'Email',
            'Document',
            'Terms version',
            'Event',
            'Result',
            'IP',
            'Session',
        ]], $this->events->map(fn ($event) => [
            $event->created_at?->format('Y-m-d H:i:s'),
            $event->user_name ?: trim(($event->user?->name ?? '').' '.($event->user?->surname ?? '')),
            $event->user?->email,
            $event->document?->original_name,
            'V'.$event->terms_version,
            $this->eventLabels[$event->event_type] ?? str($event->event_type)->headline()->toString(),
            $this->resultLabels[$event->operation_result] ?? str($event->operation_result)->headline()->toString(),
            $event->ip_address,
            $event->session_id,
        ])->toArray());
    }
}
