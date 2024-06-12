<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Clan>
 */
class ClanFactory extends Factory {
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array {

        $num = $this->faker->randomNumber();

        $xdd = $this->faker->randomNumber() + time();

        $name = 'Clan ' . $num;
        $slug = 'clan-' . $num . '-' . $xdd;


        return [
            'name' => $name,
            'school_id' => fake()->numberBetween(1, 10),
            'slug' => $slug
        ];
    }
}
