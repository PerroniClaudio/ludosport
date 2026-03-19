<?php

namespace Database\Seeders;

use App\Models\Academy;
use App\Models\Rank;
use App\Models\Role;
use App\Models\School;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MinorToAdultSwitchTestSeeder extends Seeder
{
    public function run(): void
    {
        $athleteRole = Role::where('name', 'athlete')->firstOrFail();
        $rank = Rank::firstOrFail();
        $academy = Academy::where('is_disabled', false)->orderBy('id')->firstOrFail();
        $school = School::where('academy_id', $academy->id)->where('is_disabled', false)->orderBy('id')->firstOrFail();

        $user = User::updateOrCreate(
            ['email' => 'minor@test1.com'],
            [
                'name' => 'Minor',
                'surname' => 'Switch',
                'password' => Hash::make('Password@2026'),
                'email_verified_at' => now(),
                'subscription_year' => now()->year,
                'academy_id' => $academy->id,
                'school_id' => $school->id,
                'nation_id' => $academy->nation_id,
                'rank_id' => $rank->id,
                'gender' => 'notsay',
                'birthday' => now()->subYears(18)->subDay()->toDateString(),
                'is_user_minor' => true,
                'has_user_uploaded_documents' => true,
                'has_admin_approved_minor' => true,
                'has_to_switch_from_minor' => true,
                'uploaded_documents_path' => null,
            ]
        );

        $user->roles()->sync([$athleteRole->id]);
        $user->academyAthletes()->sync([$academy->id => ['is_primary' => true]]);
        $user->schoolAthletes()->sync([$school->id => ['is_primary' => true]]);
    }
}
