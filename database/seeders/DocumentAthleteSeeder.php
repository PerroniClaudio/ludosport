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

class DocumentAthleteSeeder extends Seeder
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
            ['code' => 'DA'],
            ['name' => 'Document Athlete Nation']
        );

        $academy = Academy::query()->where('is_disabled', false)->orderBy('id')->first();

        if (! $academy) {
            $academy = Academy::create([
                'name' => 'Document Athlete Academy',
                'slug' => Str::slug('Document Athlete Academy'),
                'nation_id' => $nation->id,
            ]);
        }

        $school = School::query()
            ->where('academy_id', $academy->id)
            ->where('is_disabled', false)
            ->orderBy('id')
            ->first();

        if (! $school) {
            $school = School::create([
                'name' => 'Document Athlete School',
                'slug' => Str::slug('Document Athlete School'),
                'academy_id' => $academy->id,
                'nation_id' => $academy->nation_id,
            ]);
        }

        $user = User::updateOrCreate(
            ['email' => 'document-athlete@test.com'],
            [
                'name' => 'Document',
                'surname' => 'Athlete',
                'password' => Hash::make('DocumentAthlete01!'),
                'email_verified_at' => now(),
                'privacy_policy_accepted_at' => now(),
                'subscription_year' => now()->year,
                'academy_id' => $academy->id,
                'school_id' => $school->id,
                'nation_id' => $academy->nation_id,
                'rank_id' => $rank->id,
                'gender' => 'notsay',
                'birthday' => '1995-01-01',
                'is_user_minor' => false,
                'has_user_uploaded_documents' => false,
                'has_admin_approved_minor' => false,
                'has_to_switch_from_minor' => false,
                'uploaded_documents_path' => null,
                'profile_completed' => true,
            ]
        );

        $user->roles()->sync([$athleteRole->id]);
        $user->academyAthletes()->sync([$academy->id => ['is_primary' => true]]);
        $user->schoolAthletes()->sync([$school->id => ['is_primary' => true]]);
    }
}
