<?php

namespace App\Exports;

use App\Models\Clan;
use App\Models\User;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;

class UsersCourseExport implements WithMultipleSheets
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
        $filters = json_decode($this->export->filters);
        $courses = collect($filters->courses)->pluck('id')->toArray();
        $users_type = $filters->users_type ?? null;

        $sheets = [];

        foreach (Clan::whereIn('id', $courses)->get() as $course) {
            if ($users_type === 'athletes') {
                $users = $course->users->map(function ($user) {
                    $user->detected_as = 'athlete';

                    return $user;
                });
            } elseif ($users_type === 'personnel') {
                $users = $course->personnel->map(function ($user) {
                    $user->detected_as = 'personnel';

                    return $user;
                });
            } else {
                $athletes = $course->users->map(function ($user) {
                    $user->detected_as = 'athlete';

                    return $user;
                });
                $personnel = $course->personnel;
                $users = $athletes;
                foreach ($personnel as $user) {
                    $existing = $users->firstWhere('unique_code', $user->unique_code);
                    if ($existing) {
                        if (strpos($existing->detected_as, 'personnel') === false) {
                            $existing->detected_as .= ', personnel';
                        }
                    } else {
                        $user->detected_as = 'personnel';
                        $users->push($user);
                    }
                }
            }

            $usersArray = $users->map(function ($user) use ($course, $includeGender) {
                $row = [
                    $course->name,
                    $user->unique_code,
                    $user->name,
                    $user->surname,
                    $user->email,
                    $user->roles->pluck('name')->implode(', '),
                    $user->created_at,
                    $user->updated_at,
                    $user->how_found_us ?? '',
                    $user->detected_as ?? '',
                ];
                if ($includeGender) {
                    array_splice($row, 5, 0, $user->gender ?? '');
                }

                return $row;
            })->toArray();

            $sheets[] = new UsersCourseSheet($usersArray, $course->name, $includeGender);
        }

        return $sheets;
    }
}

class UsersCourseSheet implements FromArray, WithTitle
{
    private $users;

    private $courseName;

    private $includeGender;

    public function __construct($users, $courseName, $includeGender = false)
    {
        $this->users = $users;
        $this->courseName = $courseName;
        $this->includeGender = $includeGender;
    }

    public function array(): array
    {
        $headers = [
            'Course',
            'Code',
            'Name',
            'Surname',
            'Email',
            'Roles',
            'Created At',
            'Updated At',
            'How found us',
            'Athlete/Personnel',
        ];

        // Aggiunge condizionalmente l'intestazione del genere dopo l'email
        if ($this->includeGender) {
            array_splice($headers, 5, 0, 'Gender');
        }

        return array_merge([
            $headers,
        ], $this->users);
    }

    public function title(): string
    {
        // Limita la lunghezza del titolo a 31 caratteri (limite Excel)
        return mb_substr($this->courseName, 0, 31);
    }
}
