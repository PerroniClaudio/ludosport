<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WeaponFormSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        //

        $weaponForms = [
            ['name' => 'Form 1 Long Saber'],
            ['name' => 'Form 2 Long Saber'],
            ['name' => 'Form 3 Long Saber'],
            ['name' => 'Form 4 Long Saber'],
            ['name' => 'Form 5 Long Saber'],
            ['name' => 'Form 3 Dual Sabers'],
            ['name' => 'Form 4 Dual Sabers'],
            ['name' => 'Form 5 Dual Sabers'],
            ['name' => 'Form 3 Saberstaff'],
        ];

        foreach ($weaponForms as $weaponForm) {
            \App\Models\WeaponForm::create([
                'name' => $weaponForm['name'],
                'image' => 'https://via.placeholder.com/150',
            ]);
        }
    }
}
