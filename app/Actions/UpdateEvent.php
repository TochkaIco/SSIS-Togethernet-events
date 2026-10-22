<?php

declare(strict_types=1);

namespace App\Actions;

use App\EventType;
use App\Models\Event;
use App\Models\GlobalLog;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Spatie\Image\Enums\Fit;
use Spatie\Image\Image;

class UpdateEvent
{
    /**
     * @throws \Throwable
     */
    public function handle(array $attributes, Event $event): void
    {
        $data = collect($attributes)->only([
            'title', 'description', 'event_type', 'num_of_seats', 'paid_entry', 'entry_fee', 'one_hour_periods', 'interval_length', 'one_hour_periods_number', 'links', 'display_starts_at', 'event_starts_at', 'event_ends_at', 'allow_external_domains', 'is_hidden_event',
        ])->toArray();

        // Fallback to existing model values if the fields aren't present in the attributes payload
        $data['event_starts_at'] = isset($data['event_starts_at']) ? Carbon::parse($data['event_starts_at']) : $event->event_starts_at;
        $data['event_ends_at'] = isset($data['event_ends_at']) ? Carbon::parse($data['event_ends_at']) : $event->event_ends_at;

        $periodsChanged = (bool) ($attributes['one_hour_periods'] ?? $event->one_hour_periods) !== (bool) $event->one_hour_periods
            || (int) ($attributes['one_hour_periods_number'] ?? $event->one_hour_periods_number) !== (int) $event->one_hour_periods_number
            || (int) ($attributes['interval_length'] ?? $event->interval_length) !== (int) $event->interval_length
            || ! Carbon::parse($event->event_starts_at)->startOfMinute()->equalTo($data['event_starts_at']->startOfMinute());

        if ($periodsChanged && ! $event->canEditCriticalFields()) {
            throw ValidationException::withMessages([
                'event_starts_at' => __('Cannot change event timing or periods after participants have registered.'),
            ]);
        }

        if ($data['event_type'] === EventType::QR_TAG->value) {
            if (array_key_exists('num_of_seats', $data) && is_null($data['num_of_seats'])) {
                $data['num_of_seats'] = 1000000;
            }

            // If the start date is being updated, also update the title to match the new date
            if (! empty($attributes['event_starts_at'])) {
                $eventStarts = $data['event_starts_at'] instanceof \Carbon\Carbon ? $data['event_starts_at'] : \Carbon\Carbon::parse($data['event_starts_at']);
                $newDate = $eventStarts->format('Y-m-d');
                $oldDatePattern = 'QR-Tag '.\Carbon\Carbon::parse($event->event_starts_at)->format('Y-m-d');

                // If the user didn't change the title manually, or if it matches the old auto-generated pattern, update it
                if (empty($attributes['title']) || $attributes['title'] === $oldDatePattern) {
                    $data['title'] = 'QR-Tag '.$newDate;
                }
            }
        }

        if ($attributes['one_hour_periods'] ?? false) {
            $startTime = $data['event_starts_at']->copy();
            $hoursToAdd = (int) ($attributes['one_hour_periods_number'] ?? 1);
            $totalIntervalLength = (int) ($attributes['interval_length'] ?? 0) * (max(0, (int) ($attributes['one_hour_periods_number'] ?? 1) - 1));

            $data['event_ends_at'] = $startTime->addHours($hoursToAdd)->addMinutes($totalIntervalLength);
        }

        if ($attributes['image'] ?? false) {
            if ($event->image_path && Storage::disk('public')->exists($event->image_path)) {
                Storage::disk('public')->delete($event->image_path);
                if (Storage::disk('public')->exists($event->meta_image_path)) {
                    Storage::disk('public')->delete($event->meta_image_path);
                }
            }
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

        DB::transaction(function () use ($event, $data, $attributes, $periodsChanged) {
            $event->update($data);

            GlobalLog::log('Event Updated', 'event', ['event_id' => $event->id, 'title' => $event->title]);
            GlobalLog::discord_log('green', 'Event Updated', 'event', ['event_id', $event->id, 'title', $event->title]);

            if ($attributes['one_hour_periods'] ?? false) {
                if ($periodsChanged) {
                    $numPeriods = (int) ($attributes['one_hour_periods_number'] ?? 1);
                    // We always recreate periods for karaoke events to ensure correct sequence and breaks
                    $event->periods()->delete();

                    $currentStart = $data['event_starts_at']->copy();
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
                }
            } else {
                // Non-karaoke event, should have exactly one period
                $event->periods()->updateOrCreate(
                    ['number' => 1],
                    [
                        'starts_at' => $data['event_starts_at'],
                        'ends_at' => $data['event_ends_at'],
                        'type' => 'period',
                    ]
                );
            }

            // Process waiting list in case seats were added
            (new ProcessWaitingList)->handle($event);
        });
    }
}
