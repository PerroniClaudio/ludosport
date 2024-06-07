<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\User;

class UserSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        //

        User::factory()
            ->count(64)
            ->create()
            ->each(function ($user) {
                $user->schoolAthletes()->attach(1);
                $user->academyAthletes()->attach(1);
                $user->roles()->attach(7);
            });
    }
}
