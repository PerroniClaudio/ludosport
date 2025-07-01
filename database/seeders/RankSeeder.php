<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RankSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        //

        $ranks = [
            'Novice',
            'Initiate',
            'Academic',
            'Chevalier',
        ];

        foreach ($ranks as $rank) {
            \App\Models\Rank::create([
                'name' => $rank,
            ]);
        }

        // Set all users rank_id to 1
        \App\Models\User::query()->update(['rank_id' => 1]);
    }
}
