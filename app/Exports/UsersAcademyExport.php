<?php

namespace App\Exports;

use App\Models\Academy;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromArray;

class UsersAcademyExport implements FromArray {
    private $export;

    public function __construct($export) {
        $this->export = $export;
    }

    public function array(): array {
        //

        $filters = json_decode($this->export->filters);

        $academies = [];

        foreach ($filters->academies as $academy) {
            $academies[] = $academy->id;
        }

        if ($filters->users_type == "athletes") {
            $users = Academy::whereIn('id', $academies)->with('athletes')->get()->flatMap(function ($academy) {
                return $academy->athletes->map(function ($user) use ($academy) {
                    return [
                        $academy->name,
                        $user->unique_code,
                        $user->name,
                        $user->surname,
                        $user->email,
                        $user->roles->pluck('name')->implode(', '),
                        $user->created_at,
                        $user->updated_at,
                        $user->how_found_us ?? ""
                    ];
                });
            })->toArray();
        } else if ($filters->users_type == "personnel") {
            $users = Academy::whereIn('id', $academies)->with('personnel')->get()->flatMap(function ($academy) {
                return $academy->personnel->map(function ($user) use ($academy) {
                    return [
                        $academy->name,
                        $user->unique_code,
                        $user->name,
                        $user->surname,
                        $user->email,
                        $user->roles->pluck('name')->implode(', '),
                        $user->created_at,
                        $user->updated_at,
                        $user->how_found_us ?? ""
                    ];
                });
            })->toArray();
        } else {
            $users = Academy::whereIn('id', $academies)->with('athletes')->get()->flatMap(function ($academy) {
                return $academy->athletes->map(function ($user) use ($academy) {
                    return [
                        $academy->name,
                        $user->unique_code,
                        $user->name,
                        $user->surname,
                        $user->email,
                        $user->roles->pluck('name')->implode(', '),
                        $user->created_at,
                        $user->updated_at,
                        $user->how_found_us ?? ""
                    ];
                });
            })->toArray();

            $personnel = Academy::whereIn('id', $academies)->with('personnel')->get()->flatMap(function ($academy) {
                return $academy->personnel->map(function ($user) use ($academy) {
                    return [
                        $academy->name,
                        $user->unique_code,
                        $user->name,
                        $user->surname,
                        $user->email,
                        $user->roles->pluck('name')->implode(', '),
                        $user->created_at,
                        $user->updated_at,
                        $user->how_found_us ?? ""
                    ];
                });
            })->toArray();

            // Unisci i due array
            $allUsers = array_merge($users, $personnel);

            // La parte per filtrare i duplicati non c'era e non l'hanno chiesta ancora.
            // Usa un array associativo temporaneo per filtrare i duplicati tra atleti e personale almeno nella stessa accademia, poi se vogliono anche tra accademie diverse si può fare.
            $uniqueUsers = [];
            // Itera su tutti gli utenti e usa nome accademia e unique_code come chiave per garantire l'unicità all'interno della stessa accademia
            // Se vogliono che non si ripeta su più accademie lo stesso utente, ma basta solo che sia in una, allora usare solo $user[1] (unique_code) come chiave
            foreach ($allUsers as $user) {
                $uniqueUsers[$user[0] . $user[1]] = $user;
            }
            // Ottieni solo i valori (gli utenti unici)
            $users = array_values($uniqueUsers);
            
        }

        $users = array_map(function ($user) {
            $userObject = User::where('unique_code', $user[1])->first();
            // Aggiunta totale punti arena e punti stile associati all'utente
            $eventResults = $userObject->eventResults()
                ->whereHas('event', function ($query) {
                    $query->where('end_date', '<', now()->format('Y-m-d'))
                          ->where('is_disabled', false);
                })
                ->get();

            $total_war_points = $eventResults->sum('total_war_points');
            $total_style_points = $eventResults->sum('total_style_points');
            
           return array_merge($user, [$total_war_points, $total_style_points]); 
        }, $users);

        return [
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
                "Total Arena Points",
                "Total Style Points"
            ],
            $users
        ];
    }
}
