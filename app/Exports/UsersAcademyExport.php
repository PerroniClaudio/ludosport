<?php

namespace App\Exports;

use App\Models\Academy;
use App\Models\User;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;

class UsersAcademyExport implements WithMultipleSheets {
    use Exportable;

    private $export;

    public function __construct($export) {
        $this->export = $export;
    }

    public function sheets(): array
    {
        $filters = json_decode($this->export->filters);
        $academies = collect($filters->academies)->pluck('id')->toArray();
        $users_type = $filters->users_type ?? null;

        $sheets = [];

        foreach (Academy::whereIn('id', $academies)->get() as $academy) {
            // Prendi gli utenti in base al tipo richiesto
            if ($users_type === "athletes") {
                $users = $academy->athletes->map(function ($user) {
                    $user->detected_as = "athlete";
                    return $user;
                });
            } elseif ($users_type === "personnel") {
                $users = $academy->personnel->map(function ($user) {
                    $user->detected_as = "personnel";
                    return $user;
                });
            } else {
                // entrambi
                $athletes = $academy->athletes->map(function ($user) {
                    $user->detected_as = "athlete";
                    return $user;
                });
                $personnel = $academy->personnel;
                $users = $athletes;
                foreach ($personnel as $user) {
                    $existing = $users->firstWhere('unique_code', $user->unique_code);
                    if ($existing) {
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
            $usersArray = $users->map(function ($user) use ($academy, $users_type) {
                $eventResults = $user->eventResults()
                    ->whereHas('event', function ($query) {
                        $query->where('end_date', '<', now()->format('Y-m-d'))
                            ->where('is_disabled', false);
                    })
                    ->get();
                $total_war_points = $eventResults->sum('total_war_points');
                $total_style_points = $eventResults->sum('total_style_points');

                return [
                    $academy->name,
                    $user->unique_code,
                    $user->name,
                    $user->surname,
                    $user->email,
                    $user->roles->pluck('name')->implode(', '),
                    $user->created_at,
                    $user->updated_at,
                    $user->how_found_us ?? "",
                    $user->detected_as,
                    $users_type != "personnel"
                        ? ($user->schoolAthletes
                            ? $user->schoolAthletes->filter(function ($school) use ($academy) {
                                return $school->academy_id == $academy->id;
                            })->pluck('name')->implode(', ')
                            : "")
                        : "",
                    $users_type != "athletes"
                        ? ($user->schoolPersonnel
                            ? $user->schoolPersonnel->filter(function ($school) use ($academy) {
                                return $school->academy_id == $academy->id;
                            })->pluck('name')->implode(', ')
                            : "")
                        : "",
                    $total_war_points,
                    $total_style_points
                ];
            })->toArray();

            $sheets[] = new UsersAcademySheet($usersArray, $academy->name);
        }

        return $sheets;
    }
}

class UsersAcademySheet implements FromArray, WithTitle
{
    private $users;
    private $academyName;

    public function __construct($users, $academyName)
    {
        $this->users = $users;
        $this->academyName = $academyName;
    }

    public function array(): array
    {
        return array_merge([
            [
                "Academy",
                "Code",
                "Name",
                "Surname",
                "Email",
                "Roles",
                "Created At",
                "Updated At",
                "How found us",
                "Athlete/Personnel",
                "Schools as athlete",
                "Schools as personnel",
                "Total Arena Points",
                "Total Style Points"
            ]
        ], $this->users);
    }

    public function title(): string
    {
        // Limita la lunghezza del titolo a 31 caratteri (limite Excel)
        return mb_substr($this->academyName, 0, 31);
    }
}
