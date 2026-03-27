<?php

namespace App\Exports;

use App\Models\School;
use Maatwebsite\Excel\Concerns\FromArray;

class SchoolsExport implements FromArray
{
    private $export;

    public function __construct($export)
    {
        $this->export = $export;
    }

    public function array(): array
    {
        $filters = json_decode($this->export->filters);
        $academyIds = collect($filters->academies ?? [])->pluck('id')->filter()->unique()->values()->toArray();

        $schools = School::query()
            ->with(['academy', 'nation'])
            ->where('is_disabled', '0')
            ->when(count($academyIds) > 0, function ($query) use ($academyIds) {
                $query->whereIn('academy_id', $academyIds);
            })
            ->orderBy('academy_id')
            ->orderBy('name')
            ->get();

        $rows = $schools->map(function ($school) {
            return [
                $school->id,
                $school->name,
                $school->email ?? '',
                $school->nation?->name ?? '',
                $school->academy?->name ?? '',
                $school->address ?? '',
                $school->city ?? '',
                $school->state ?? '',
                $school->zip ?? '',
                $school->country ?? '',
            ];
        })->toArray();

        return array_merge([
            [
                'ID',
                'School',
                'Email',
                'Nation',
                'Academy',
                'Address',
                'City',
                'State',
                'ZIP',
                'Country',
            ],
        ], $rows);
    }
}
