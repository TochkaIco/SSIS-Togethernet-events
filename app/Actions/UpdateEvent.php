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
            'title', 'description', 'event_type', 'num_of_seats', 'paid_entry', 'entry_fee', 'links', 'display_starts_at', 'event_starts_at',
        ])->toArray();

        if ($attributes['one_hour_periods'] ?? false) {
            $startTime = Carbon::parse($data['event_starts_at']);
            $hoursToAdd = (int) ($attributes['one_hour_periods_number'] ?? 1);
            $totalIntervalLength = (int) ($attributes['interval_length'] ?? 1) * ((int) ($attributes['one_hour_periods_number'] ?? 1) - 1);

            $data['event_ends_at'] = $startTime->addHours($hoursToAdd)->addMinutes($totalIntervalLength);
        }

        if ($attributes['image'] ?? false) {
            $data['image_path'] = $attributes['image']->store('events', 'public');
        }

        DB::transaction(function () use ($event, $data) {
            $event->update($data);
        });
    }
}
