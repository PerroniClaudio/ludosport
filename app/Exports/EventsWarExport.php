<?php

namespace App\Exports;

use App\Models\EventResult;
use App\Models\User;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;

class EventsWarExport implements WithMultipleSheets
{
    use Exportable;

    private $export;

    private $exportUser;

    private $exportUserRole;

    public function __construct($export)
    {
        $this->export = $export;
        $this->exportUser = User::find($this->export->user_id);
        $this->exportUserRole = $this->export->userRole?->name;
    }

    public function sheets(): array
    {
        $includeGender = $this->exportUserRole === 'admin';
        $sheets = [];

        $filters = collect(json_decode($this->export->filters)->filters);

        foreach ($filters as $event) {

            $users = EventResult::where('event_id', '=', $event->id)->with('user')->get()->map(function ($event_result) use ($includeGender) {
                $row = [
                    $event_result->user->unique_code,
                    $event_result->user->name,
                    $event_result->user->surname,
                    $event_result->war_points,
                ];
                if ($includeGender) {
                    array_splice($row, 3, 0, $event_result->user->gender ?? '');
                }

                return $row;
            })->toArray();

            $sheets[] = new EventsWarPointsSheet($users, $event->id, $event->name, $includeGender);
        }

        return $sheets;
    }
}

class EventsWarPointsSheet implements FromArray, WithTitle
{
    private $users;

    private $event_id;

    private $event_name;

    private $includeGender;

    public function __construct($users, $event_id, $event_name, $includeGender)
    {
        $this->users = $users;
        $this->event_id = $event_id;
        $this->event_name = $event_name;
        $this->includeGender = $includeGender;
    }

    public function array(): array
    {
        $headers = [
            'Code',
            'Name',
            'Surname',
            'Arena Points',
        ];
        if ($this->includeGender) {
            array_splice($headers, 3, 0, 'Gender');
        }

        return array_merge([
            $headers,
        ], $this->users);
    }

    public function title(): string
    {
        return 'Event '.$this->event_id.' - '.$this->event_name;
    }
}
