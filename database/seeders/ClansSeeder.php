<?php

namespace Database\Seeders;

use App\Models\Academy;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\School;
use App\Models\Clan;
use App\Models\User;

class ClansSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {

        // Create 10 academies
        $academies = Academy::factory()->count(10)->create();

        // Create 10 schools
        $schools = School::factory()->count(10)->create();

        foreach ($schools as $school) {
            // Create 5 clans for each school
            $clans = Clan::factory()->count(5)->create([
                'school_id' => $school->id,
            ]);

            foreach ($clans as $clan) {
                // Assign users as athletes to each clan
                $users = User::whereHas('roles', function ($query) {
                    $query->where('name', 'athlete');
                })->inRandomOrder()->limit(10)->get();

                $clan->users()->syncWithoutDetaching($users->id);

                $instructor = User::whereHas('roles', function ($query) {
                    $query->where('name', 'instructor');
                })->inRandomOrder()->first();

                $clan->personnel()->syncWithoutDetaching($instructor->id);
            }
        }
    }
}
