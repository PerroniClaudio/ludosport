<?php

namespace Database\Seeders;

use App\Models\Academy;
use Illuminate\Database\Seeder;


class AcademySeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        //

        Academy::create([
            'name' => 'No academy',
            'slug' => 'no-academy',
            'nation_id' => 1,
        ]);
    }
}
