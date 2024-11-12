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
            [
                'name' => 'Form 1',
                'image' => '/weapon-forms/form_1.webp'
            ],
            [
                'name' => 'Form 2',
                'image' => '/weapon-forms/form_2.webp'
            ],
            [
                'name' => 'Form Y',
                'image' => '/weapon-forms/form_y.webp'
            ],
            [
                'name' => 'Form 3 Long Saber',
                'image' => '/weapon-forms/long_saber/form_3.webp'
            ],
            [
                'name' => 'Form 4 Long Saber',
                'image' => '/weapon-forms/long_saber/form_4.webp'
            ],
            [
                'name' => 'Form 5 Long Saber',
                'image' => '/weapon-forms/long_saber/form_5.webp'
            ],

            [
                'name' => 'Form 3 Dual Sabers',
                'image' => '/weapon-forms/dual_saber/form_3.webp'
            ],
            [
                'name' => 'Form 4 Dual Sabers',
                'image' => '/weapon-forms/dual_saber/form_4.webp'
            ],
            [
                'name' => 'Form 5 Dual Sabers',
                'image' => '/weapon-forms/dual_saber/form_5.webp'
            ],

            [
                'name' => 'Form 3 Saberstaff',
                'image' => '/weapon-forms/saberstaff/form_3.webp'
            ],
            [
                'name' => 'Form 4 Saberstaff',
                'image' => '/weapon-forms/saberstaff/form_4.webp'
            ],
            [
                'name' => 'Form 5 Saberstaff',
                'image' => '/weapon-forms/saberstaff/form_5.webp'
            ],

        ];

        foreach ($weaponForms as $weaponForm) {
            \App\Models\WeaponForm::create([
                'name' => $weaponForm['name'],
                'image' => $weaponForm['image']
            ]);
        }
    }
}
