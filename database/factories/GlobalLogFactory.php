<?php

namespace Database\Factories;

use App\Models\GlobalLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GlobalLog>
 */
class GlobalLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'action_title' => $this->faker->sentence(),
            'action_type' => $this->faker->randomElement(['event', 'meeting', 'user', 'system']),
            'details' => [],
            'user_id' => User::factory(),
        ];
    }
}
