<?php

namespace Database\Seeders;

use App\Models\Academy;
use App\Models\Rank;
use App\Models\Role;
use App\Models\School;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MinorApprovalTestSeeder extends Seeder
{
    public function run(): void
    {
        $academy = Academy::findOrFail(2);
        $school = School::findOrFail(2);
        $rectorRole = Role::where('name', 'rector')->firstOrFail();
        $athleteRole = Role::where('name', 'athlete')->firstOrFail();
        $rank = Rank::firstOrFail();
        $nationId = $academy->nation_id ?? 1;

        $rector = User::updateOrCreate(
            ['email' => 'rector@ludosport.com'],
            [
                'name' => 'Test',
                'surname' => 'Rector',
                'password' => Hash::make('Rector@2026'),
                'email_verified_at' => now(),
                'subscription_year' => 2026,
                'academy_id' => $academy->id,
                'school_id' => $school->id,
                'nation_id' => $nationId,
                'rank_id' => $rank->id,
                'gender' => 'notsay',
                'birthday' => '1990-01-01',
                'is_user_minor' => false,
                'has_user_uploaded_documents' => false,
                'has_admin_approved_minor' => false,
                'uploaded_documents_path' => null,
            ]
        );

        $rector->roles()->syncWithoutDetaching([$rectorRole->id]);
        $rector->academies()->syncWithoutDetaching([$academy->id => ['is_primary' => true]]);

        for ($i = 1; $i <= 5; $i++) {
            $user = User::updateOrCreate(
                ['email' => "minor.test{$i}@ludosport.com"],
                [
                    'name' => 'Minor',
                    'surname' => "Test {$i}",
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'subscription_year' => 2026,
                    'academy_id' => $academy->id,
                    'school_id' => $school->id,
                    'nation_id' => $nationId,
                    'rank_id' => $rank->id,
                    'gender' => 'notsay',
                    'birthday' => '2012-01-0' . $i,
                    'is_user_minor' => true,
                    'has_user_uploaded_documents' => false,
                    'has_admin_approved_minor' => false,
                    'uploaded_documents_path' => null,
                ]
            );

            $user->roles()->syncWithoutDetaching([$athleteRole->id]);
            $user->academyAthletes()->syncWithoutDetaching([$academy->id => ['is_primary' => true]]);
            $user->schoolAthletes()->syncWithoutDetaching([$school->id => ['is_primary' => true]]);
        }
    }
}
