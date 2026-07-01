<?php

namespace Database\Seeders;

use App\Models\Academy;
use App\Models\Nation;
use App\Models\Rank;
use App\Models\Role;
use App\Models\School;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TestProfileAthleteSeeder extends Seeder
{
    public function run(): void
    {
        config(['scout.driver' => 'null']);

        $athleteRole = Role::firstOrCreate(
            ['name' => 'athlete'],
            ['prefix' => 'athlete', 'label' => 'athlete']
        );

        $rank = Rank::firstOrCreate(['name' => 'Novice']);

        $nation = Nation::firstOrCreate(
            ['code' => 'TP'],
            ['name' => 'Test Profile Nation']
        );

        $academy = Academy::orderBy('id')->first();

        if (! $academy) {
            $academy = Academy::create([
                'name' => 'Test Profile Academy',
                'slug' => Str::slug('Test Profile Academy'),
                'nation_id' => $nation->id,
            ]);
        }

        $school = School::where('academy_id', $academy->id)->orderBy('id')->first();

        if (! $school) {
            $school = School::create([
                'name' => 'Test Profile School',
                'slug' => Str::slug('Test Profile School'),
                'academy_id' => $academy->id,
                'nation_id' => $academy->nation_id,
            ]);
        }

        $user = User::updateOrCreate(
            ['email' => 'test-profile@test.com'],
            [
                'name' => 'Test',
                'surname' => 'Profile',
                'password' => Hash::make('TestProfile01!'),
                'email_verified_at' => now(),
                'subscription_year' => now()->year,
                'academy_id' => $academy->id,
                'school_id' => $school->id,
                'nation_id' => $academy->nation_id,
                'rank_id' => $rank->id,
                'gender' => 'notsay',
                'birthday' => null,
                'is_user_minor' => false,
                'has_user_uploaded_documents' => false,
                'has_admin_approved_minor' => false,
                'has_to_switch_from_minor' => false,
                'uploaded_documents_path' => null,
                'profile_completed' => false,
            ]
        );

        $user->roles()->sync([$athleteRole->id]);
        $user->academyAthletes()->sync([$academy->id => ['is_primary' => true]]);
        $user->schoolAthletes()->sync([$school->id => ['is_primary' => true]]);
    }
}
