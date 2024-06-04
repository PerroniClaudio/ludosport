<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EventResult>
 */
class EventResultFactory extends Factory {
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array {

        $event = \App\Models\Event::inRandomOrder()->first();
        $user = \App\Models\User::inRandomOrder()->first();

        $war_points = $this->faker->numberBetween(1, 64);
        $style_points = $this->faker->numberBetween(1, 64);

        return [
            //
            'event_id' => $event->id,
            'user_id' => $user->id,
            'war_points' => $war_points,
            'style_points' => $style_points,
            'bonus_war_points' => 0,
            'bonus_style_points' => 0,
            'total_war_points' => $war_points,
            'total_style_points' => $style_points,
        ];
    }
}
