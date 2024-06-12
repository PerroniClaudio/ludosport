<?php

namespace Database\Factories;

use App\Models\Academy;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\School>
 */
class SchoolFactory extends Factory {
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array {
        $number = fake()->randomNumber();

        $xdd = fake()->randomNumber() + time();
        $slug = 'school-' . $number . '-' . $xdd;

        return [
            //
            'name' => 'School ' . $number,
            "nation_id" => 2,
            "slug" => $slug,
            "academy_id" => Academy::inRandomOrder()->first()->id
        ];
    }
}
