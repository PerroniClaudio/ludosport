<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Academy>
 */
class AcademyFactory extends Factory
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
            "name" => "Academy " . $number,
            "nation_id" => 2,
            "slug" => "academy-" . $number
        ];
    }
}
