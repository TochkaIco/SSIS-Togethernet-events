<?php

namespace Database\Factories;

use App\EventType;
use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

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
            'num_of_seats' => 20,
            'paid_entry' => true,
            'entry_fee' => 100,
            'display_starts_at' => now(),
            'event_starts_at' => now()->addDay(),
            'event_ends_at' => now()->addDay()->addHours(2),
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Event $event) {
            if ($event->one_hour_periods) {
                $currentStart = Carbon::parse($event->event_starts_at);
                $numPeriods = $event->one_hour_periods_number ?? 1;
                $interval = $event->interval_length ?? 0;

                for ($i = 1; $i <= $numPeriods; $i++) {
                    $periodEnd = $currentStart->copy()->addHour();

                    $event->periods()->create([
                        'starts_at' => $currentStart,
                        'ends_at' => $periodEnd,
                        'type' => 'period',
                        'number' => $i,
                    ]);

                    $currentStart = $periodEnd->copy()->addMinutes($interval);

                    if ($interval > 0 && $i < $numPeriods) {
                        $event->periods()->create([
                            'starts_at' => $periodEnd,
                            'ends_at' => $currentStart,
                            'type' => 'break',
                        ]);
                    }
                }
            } else {
                $event->periods()->create([
                    'starts_at' => $event->event_starts_at,
                    'ends_at' => $event->event_ends_at,
                    'type' => 'period',
                    'number' => 1,
                ]);
            }
        });
    }
}
