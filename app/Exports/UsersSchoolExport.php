<?php

namespace App\Exports;

use App\Models\School;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;

class UsersSchoolExport implements WithMultipleSheets {
    use Exportable;
    
    private $export;

    public function __construct($export) {
        $this->export = $export;
    }

    public function sheets(): array
    {
        $filters = json_decode($this->export->filters);
        $schools = collect($filters->schools)->pluck('id')->toArray();
        $users_type = $filters->users_type ?? null;

        $sheets = [];

        foreach (School::whereIn('id', $schools)->get() as $school) {
            // Prendi gli utenti in base al tipo richiesto
            if ($users_type === "athletes") {
                $users = $school->athletes->map(function ($user) {
                    $user->detected_as = "athlete";
                    return $user;
                });
            } elseif ($users_type === "personnel") {
                $users = $school->personnel->map(function ($user) {
                    $user->detected_as = "personnel";
                    return $user;
                });
            } else {
                // entrambi
                $athletes = $school->athletes;
                $personnel = $school->personnel;
                $users = $athletes->map(function ($user) {
                    $user->detected_as = "athlete";
                    return $user;
                });
                foreach ($personnel as $user) {
                    // Check if the user is already in $users by unique_code
                    $existing = $users->firstWhere('unique_code', $user->unique_code);
                    if ($existing) {
                        // Append ", personnel" to detected_as if not already present
                        if (strpos($existing->detected_as, 'personnel') === false) {
                            $existing->detected_as .= ', personnel';
                        }
                    } else {
                        $user->detected_as = "personnel";
                        $users->push($user);
                    }
                }
            }

            // Mappa i dati degli utenti
            $usersArray = $users->map(function ($user) use ($school, $users_type) {
                $eventResults = $user->eventResults()
                    ->whereHas('event', function ($query) {
                        $query->where('end_date', '<', now()->format('Y-m-d'))
                            ->where('is_disabled', false);
                    })
                    ->get();
                // Calcola i punti totali
                $total_war_points = $eventResults->sum('total_war_points');
                $total_style_points = $eventResults->sum('total_style_points');

                return [
                    $school->name,
                    $user->unique_code,
                    $user->name,
                    $user->surname,
                    $user->email,
                    $user->roles->pluck('name')->implode(', '),
                    $user->created_at,
                    $user->updated_at,
                    $user->how_found_us ?? "",
                    $user->detected_as,
                    $total_war_points,
                    $total_style_points
                ];
            })->toArray();

            $sheets[] = new UsersSchoolSheet($usersArray, $school->name);
        }

        return $sheets;
    }
}

class UsersSchoolSheet implements FromArray, WithTitle
{
    private $users;
    private $schoolName;

    public function __construct($users, $schoolName)
    {
        $this->users = $users;
        $this->schoolName = $schoolName;
    }

    public function array(): array
    {
        return array_merge([
            [
                "School",
                "Code",
                "Name",
                "Surname",
                "Email",
                "Roles",
                "Created At",
                "Updated At",
                "How found us",
                "Athlete/Personnel",
                "Total Arena Points",
                "Total Style Points"
            ]
        ], $this->users);
    }

    public function title(): string
    {
        // Limita la lunghezza del titolo a 31 caratteri (limite Excel)
        return mb_substr($this->schoolName, 0, 31);
    }
}

