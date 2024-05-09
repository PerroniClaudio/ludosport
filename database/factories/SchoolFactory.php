<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\School>
 */
class SchoolFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $number = fake()->randomNumber();

        return [
            //
            'name' => 'School ' . $number,
            "nation_id" => 2,
            "slug" => "school-" . $number,
            "academy_id" => fake()->numberBetween(1, 10)
        ];
    }
}
