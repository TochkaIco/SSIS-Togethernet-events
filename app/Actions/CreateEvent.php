<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Event;
use Illuminate\Support\Facades\DB;

class CreateEvent
{
    /**
     * @throws \Throwable
     */
    public function handle(array $attributes): void
    {
        $data = collect($attributes)->only([
            'title', 'description', 'event_type', 'num_of_seats', 'paid_entry', 'entry_fee', 'links', 'display_starts_at', 'event_starts_at', 'event_ends_at',
        ])->toArray();

        if ($attributes['image'] ?? false) {
            $data['image_path'] = $attributes['image']->store('events', 'public');
        }

        DB::transaction(function () use ($data) {
            Event::create($data);
        });
    }
}
