<?php

namespace Database\Factories;

use DateInterval;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory {
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array {

        $is_approved = $this->faker->boolean();
        $is_published = false;
        $name = 'Event ' . $this->faker->unique()->numberBetween(1, 100);
        $slug = Str::slug($name);

        if ($is_approved) {
            $is_published = $this->faker->boolean();
        }

        $start_date = $this->faker->dateTimeBetween('-6 month', 'now');
        $daysToAdd = rand(0, 3);
        $end_date = (clone $start_date)->modify("+{$daysToAdd} days");

        return [
            //
            'name' => $name,
            'slug' => $slug,
            'description' => '<p>' . $this->faker->text() . '</p>',
            'thumbnail' => '',
            'user_id' => 6,
            'is_approved' => $is_approved,
            'is_published' => $is_published,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'location' => '',
            'nation_id' => 2,
            'academy_id' => $this->faker->numberBetween(1, 11),
            'school_id' => $this->faker->numberBetween(1, 10),
            'event_type' => $this->faker->numberBetween(1, 3),
        ];
    }
}
