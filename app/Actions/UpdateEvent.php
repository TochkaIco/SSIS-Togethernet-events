<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Event;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class UpdateEvent
{
    /**
     * @throws \Throwable
     */
    public function handle(array $attributes, Event $event): void
    {
        $data = collect($attributes)->only([
            'title', 'description', 'event_type', 'num_of_seats', 'paid_entry', 'entry_fee', 'one_hour_periods', 'interval_length', 'one_hour_periods_number', 'links', 'display_starts_at', 'event_starts_at', 'event_ends_at',
        ])->toArray();

        if ($attributes['one_hour_periods'] ?? false) {
            $startTime = Carbon::parse($data['event_starts_at']);
            $hoursToAdd = (int) ($attributes['one_hour_periods_number'] ?? 1);
            $totalIntervalLength = (int) ($attributes['interval_length'] ?? 0) * (max(0, (int) ($attributes['one_hour_periods_number'] ?? 1) - 1));

            $data['event_ends_at'] = $startTime->addHours($hoursToAdd)->addMinutes($totalIntervalLength);
        }

        if ($attributes['image'] ?? false) {
            $data['image_path'] = $attributes['image']->store('events', 'public');
        }

        DB::transaction(function () use ($event, $data, $attributes) {
            $event->update($data);

            if ($attributes['one_hour_periods'] ?? false) {
                $numPeriods = (int) ($attributes['one_hour_periods_number'] ?? 1);
                // We always recreate periods for karaoke events to ensure correct sequence and breaks
                $event->periods()->delete();

                $currentStart = Carbon::parse($data['event_starts_at']);
                $interval = (int) ($attributes['interval_length'] ?? 0);

                for ($i = 1; $i <= $numPeriods; $i++) {
                    $periodEnd = $currentStart->copy()->addHour();

                    $event->periods()->create([
                        'starts_at' => $currentStart,
                        'ends_at' => $periodEnd,
                        'type' => 'period',
                        'number' => $i,
                    ]);

                    $currentStart = $periodEnd->copy()->addMinutes($interval);

                    // Add break if there's an interval and it's not the last period
                    if ($interval > 0 && $i < $numPeriods) {
                        $event->periods()->create([
                            'starts_at' => $periodEnd,
                            'ends_at' => $currentStart,
                            'type' => 'break',
                        ]);
                    }
                }
            } else {
                // Non-karaoke event, should have exactly one period
                $period = $event->periods()->first();
                if ($period) {
                    $period->update([
                        'starts_at' => $data['event_starts_at'],
                        'ends_at' => $data['event_ends_at'],
                        'type' => 'period',
                        'number' => 1,
                    ]);
                } else {
                    $event->periods()->create([
                        'starts_at' => $data['event_starts_at'],
                        'ends_at' => $data['event_ends_at'],
                        'type' => 'period',
                        'number' => 1,
                    ]);
                }
            }

            // Process waiting list in case seats were added
            (new ProcessWaitingList)->handle($event);
        });
    }
}
