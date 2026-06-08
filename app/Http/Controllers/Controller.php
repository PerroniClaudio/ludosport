<?php

namespace App\Http\Controllers;

abstract class Controller
{
    protected function formatPublicMemberRows($members, $viewer, bool $includeRole = false): array
    {
        return $members->map(function ($member) use ($viewer, $includeRole) {
            $row = [
                'name' => $member->name,
                'surname' => $member->surname,
                'battle_name' => $member->canViewerSeeMinorBattleName($viewer) ? $member->battle_name : '',
            ];

            if ($includeRole) {
                $row['role'] = $member->role ?? '';
            }

            return $row;
        })->values()->all();
    }
}
