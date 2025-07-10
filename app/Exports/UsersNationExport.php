<?php

namespace App\Exports;

use App\Models\Nation;
use App\Models\Academy;
use App\Models\User;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;

class UsersNationExport implements WithMultipleSheets {
    use Exportable;

    private $export;

    public function __construct($export) {
        $this->export = $export;
    }

    public function sheets(): array
    {
        $filters = json_decode($this->export->filters);
        $nations = collect($filters->nations)->pluck('id')->toArray();
        $users_type = $filters->users_type ?? null;

        $sheets = [];

        foreach (Nation::whereIn('id', $nations)->get() as $nation) {
            // Prendi tutte le accademie della nazione
            $academies = Academy::where('nation_id', $nation->id)->get();

            // Collezioni utenti
            $users = collect();

            if ($users_type === "athletes") {
                foreach ($academies as $academy) {
                    foreach ($academy->athletes as $user) {
                        $user->detected_as = "athlete";
                        $users->push($user);
                    }
                }
            } elseif ($users_type === "personnel") {
                foreach ($academies as $academy) {
                    foreach ($academy->personnel as $user) {
                        $existing = $users->firstWhere('unique_code', $user->unique_code);
                        if (!$existing) {
                          $user->detected_as = "personnel";
                          $users->push($user);
                        }
                    }
                }
            } else {
                // entrambi
                foreach ($academies as $academy) {
                    foreach ($academy->athletes as $user) {
                        $user->detected_as = "athlete";
                        $users->push($user);
                    }
                }
                foreach ($academies as $academy) {
                    foreach ($academy->personnel as $user) {
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
            }

            // Rimuovi duplicati per unique_code (anche se non dovrebbero già esserci)
            $uniqueUsers = $users->unique('unique_code')->values();

            // Mappa i dati degli utenti
            $usersArray = $uniqueUsers->map(function ($user) use ($academies, $users_type, $nation) {
                // Trova le accademie in cui è atleta/personale nella nazione
                $academiesAsAthlete = $user->academyAthletes
                    ? $user->academyAthletes->where('nation_id', $nation->id)->pluck('name')->implode(', ')
                    : "";
                $academiesAsPersonnel = $user->academies
                    ? $user->academies->where('nation_id', $nation->id)->pluck('name')->implode(', ')
                    : "";

                // Calcola i punti totali
                $eventResults = $user->eventResults()
                    ->whereHas('event', function ($query) {
                        $query->where('end_date', '<', now()->format('Y-m-d'))
                            ->where('is_disabled', false);
                    })
                    ->get();
                $total_war_points = $eventResults->sum('total_war_points');
                $total_style_points = $eventResults->sum('total_style_points');

                return [
                    $nation->name,
                    $user->unique_code,
                    $user->name,
                    $user->surname,
                    $user->email,
                    $user->roles->pluck('name')->implode(', '),
                    $user->created_at,
                    $user->updated_at,
                    $user->how_found_us ?? "",
                    $user->detected_as ?? "",
                    $academiesAsAthlete,
                    $academiesAsPersonnel,
                    $total_war_points,
                    $total_style_points
                ];
            })->toArray();

            $sheets[] = new UsersNationSheet($usersArray, $nation->name);
        }

        return $sheets;
    }
}

class UsersNationSheet implements FromArray, WithTitle
{
    private $users;
    private $nationName;

    public function __construct($users, $nationName)
    {
        $this->users = $users;
        $this->nationName = $nationName;
    }

    public function array(): array
    {
        return array_merge([
            [
                "Nation",
                "Code",
                "Name",
                "Surname",
                "Email",
                "Roles",
                "Created At",
                "Updated At",
                "How found us",
                "Athlete/Personnel",
                "Academy as athlete",
                "Academies as personnel",
                "Total Arena Points",
                "Total Style Points"
            ]
        ], $this->users);
    }

    public function title(): string
    {
        // Limita la lunghezza del titolo a 31 caratteri (limite Excel)
        return mb_substr($this->nationName, 0, 31);
    }
}
