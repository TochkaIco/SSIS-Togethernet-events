<?php

declare(strict_types=1);

namespace App\Actions;

use App\EventType;
use App\Models\Event;
use App\Models\GlobalLog;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\Image\Enums\Fit;
use Spatie\Image\Image;

class CreateEvent
{
    /**
     * @throws \Throwable
     */
    public function handle(array $attributes): void
    {
        $data = collect($attributes)->only([
            'title', 'description', 'event_type', 'num_of_seats', 'paid_entry', 'entry_fee', 'one_hour_periods', 'interval_length', 'one_hour_periods_number', 'links', 'display_starts_at', 'event_starts_at', 'event_ends_at', 'allow_external_domains', 'is_hidden_event',
        ])->toArray();

        if ($data['event_type'] === EventType::QR_TAG->value) {
            if (empty($data['title'])) {
                $data['title'] = 'QR-Tag '.Carbon::parse($data['event_starts_at'])->format('Y-m-d');
            }

            if (empty($data['description'])) {
                $data['description'] = __('QRTag is a digital version of the "Killer Game" and a great way to meet other students at school.');
            }

            if (! isset($data['num_of_seats'])) {
                $data['num_of_seats'] = 1000000;
            }
        }

        if ($attributes['one_hour_periods'] ?? false) {
            $startTime = Carbon::parse($data['event_starts_at']);
            $hoursToAdd = (int) ($attributes['one_hour_periods_number'] ?? 1);
            $totalIntervalLength = (int) ($attributes['interval_length'] ?? 0) * (max(0, (int) ($attributes['one_hour_periods_number'] ?? 1) - 1));

            $data['event_ends_at'] = $startTime->addHours($hoursToAdd)->addMinutes($totalIntervalLength);
        }

        if ($attributes['image'] ?? false) {
            // Store the main version of the image
            $originalPath = $attributes['image']->store('events', 'public');
            $originalFull = Storage::disk('public')->path($originalPath);
            $webpPath = pathinfo($originalPath, PATHINFO_DIRNAME).'/'.pathinfo($originalPath, PATHINFO_FILENAME).'.webp';
            Image::load($originalFull)
                ->fit(Fit::Crop, 1200, 900)
                ->format('webp')
                ->save(Storage::disk('public')->path($webpPath));
            $data['image_path'] = $webpPath;
            // Store the meta image used in embeds
            $pngPath = pathinfo($originalPath, PATHINFO_DIRNAME).'/'.pathinfo($originalPath, PATHINFO_FILENAME).'.png';
            Image::load($originalFull)
                ->fit(Fit::Crop, 1200, 630)
                ->format('png')
                ->save(Storage::disk('public')->path($pngPath));
            $data['meta_image_path'] = $pngPath;
            // Delete original upload
            Storage::disk('public')->delete($originalPath);
        }

        DB::transaction(function () use ($data, $attributes) {
            $event = Event::create($data);

            GlobalLog::log('Event Created', 'event', ['event_id' => $event->id, 'title' => $event->title]);
            GlobalLog::discord_log('green', 'Event Created', 'event', ['event_id', $event->id, 'title', $event->title]);

            if ($attributes['one_hour_periods'] ?? false) {
                $currentStart = Carbon::parse($data['event_starts_at']);
                $numPeriods = (int) ($attributes['one_hour_periods_number'] ?? 1);
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
                $event->periods()->create([
                    'starts_at' => $data['event_starts_at'],
                    'ends_at' => $data['event_ends_at'],
                    'type' => 'period',
                    'number' => 1,
                ]);
            }
        });
    }
}
