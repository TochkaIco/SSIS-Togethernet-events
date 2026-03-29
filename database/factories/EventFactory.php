<?php

namespace Database\Factories;

use App\EventType;
use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'event_type' => EventType::KARAOKE->value,
            'display_starts_at' => now(),
            'event_starts_at' => now()->addDay(),
            'event_ends_at' => now()->addDay()->addHours(2),
        ];
    }
}
