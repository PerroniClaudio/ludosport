<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromArray;

class UsersExport implements FromArray
{
    private $export;

    private $exportUser;

    private $exportUserRole;

    public function __construct($export)
    {
        $this->export = $export;
        $this->exportUser = User::find($this->export->user_id);
        $this->exportUserRole = $this->export->userRole?->name;
    }

    public function array(): array
    {
        //
        $includeGender = $this->exportUserRole === 'admin';
        $filters = json_decode($this->export->filters);
        $usersCollection = User::where('created_at', '>=', $filters->start_date)->where('created_at', '<=', $filters->end_date)->with('roles')->get();

        $users = $usersCollection->map(function ($user) use ($includeGender) {
            $row = [
                $user->unique_code,
                $user->name,
                $user->surname,
                $user->email,
                $user->roles->map(function ($role) {
                    return $role->name;
                })->implode(', '),
                $user->created_at,
                $user->updated_at,
                $user->how_found_us ?? '',
            ];

            if ($includeGender) {
                array_splice($row, 4, 0, $user->gender ?? '');
            }

            return $row;
        })->toArray();

        $headers = [
            'Code',
            'Name',
            'Surname',
            'Email',
            'Roles',
            'Created At',
            'Updated At',
            'How found us',
        ];

        if ($includeGender) {
            array_splice($headers, 4, 0, 'Gender');
        }

        return array_merge([
            $headers,
        ], $users);
    }
}
