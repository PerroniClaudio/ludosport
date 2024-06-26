<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory {
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array {

        $fullname = $this->faker->name();
        $name = explode(' ', $fullname)[0];
        $surname = explode(' ', $fullname)[1];

        $unique_code = Str::random(4) . "-" . Str::random(4) . "-" . Str::random(4) . "-" . Str::random(4);

        return [
            'name' => $name,
            'surname' => $surname,
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'subscription_year' => $this->faker->numberBetween(2020, 2024),
            'unique_code' => $unique_code,
            'nation_id' => $this->faker->numberBetween(1, 10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
