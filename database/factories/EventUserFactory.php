<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\EventUser;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventUser>
 */
class EventUserFactory extends Factory
{
    protected $model = EventUser::class;

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
            'has_paid' => $this->faker->boolean(70),
            'has_arrived' => $this->faker->boolean(30),
            'in_waitinglist' => false,
        ];
    }

    /**
     * State for users on the waiting list.
     */
    public function waitingList(): static
    {
        return $this->state(fn (array $attributes) => [
            'in_waitinglist' => true,
            'has_paid' => false,
            'has_arrived' => false,
        ]);
    }

    /**
     * State for users who have actually arrived.
     */
    public function arrived(): static
    {
        return $this->state(fn (array $attributes) => [
            'has_arrived' => true,
            'has_paid' => true,
        ]);
    }
}
