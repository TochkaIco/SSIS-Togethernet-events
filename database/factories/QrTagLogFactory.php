<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\QrTagLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QrTagLog>
 */
class QrTagLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'user_id' => User::factory(),
            'target_user_id' => User::factory(),
            'admin_id' => null,
            'type' => 'tagged',
            'data' => [],
        ];
    }

    public function started(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'started',
            'target_user_id' => null,
        ]);
    }

    public function tagged(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'tagged',
        ]);
    }
}
