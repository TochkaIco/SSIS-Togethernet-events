<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Events\Tabs;

use App\Models\Event;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * @property array<string, mixed> $stats
 */
class ViewDetails extends Component
{
    public Event $event;

    #[Computed]
    public function stats(): array
    {
        $registrationsCount = $this->event->participants()->count();

        $userIds = $this->event->participants()
            ->pluck('user_id');

        $classData = User::whereIn('id', $userIds)
            ->selectRaw('class, COUNT(*) as count')
            ->groupBy('class')
            ->get();

        $eventStart = Carbon::parse($this->event->event_starts_at)->getTimestamp() * 1000;
        $eventCreated = Carbon::parse($this->event->created_at)->getTimestamp() * 1000;
        $displayStartsAt = Carbon::parse($this->event->display_starts_at)->getTimestamp() * 1000;
        $eventEndsAt = Carbon::parse($this->event->event_ends_at)->getTimestamp() * 1000;

        $paidCount = $this->event->participants()->where('has_paid', true)->count();
        $arrivedCount = $this->event->participants()->where('has_arrived', true)->count();

        $entryFeeRevenue = $this->event->isFree() ? 0.0 : round((float) $this->event->entry_fee * $paidCount, 2);
        $kioskRevenue = round((float) ($this->event->kiosk?->purchases()->sum('cost') ?? 0), 2);

        $statusTimelines = [];

        if (! $this->event->isFree()) {
            $statusTimelines[] = [
                'id' => 'paymentTimelineChart',
                'label' => __('Payment Timeline'),
                'series_label' => __('Paid'),
                'color' => '#22c55e',
                'background' => 'rgba(34, 197, 94, 0.1)',
                'times' => $this->participantTimestamps('paid_at'),
            ];
        }

        $statusTimelines[] = [
            'id' => 'arrivalTimelineChart',
            'label' => __('Arrival Timeline'),
            'series_label' => __('Arrived'),
            'color' => '#8b5cf6',
            'background' => 'rgba(139, 92, 246, 0.1)',
            'times' => $this->participantTimestamps('arrived_at'),
        ];

        if ($this->event->isLan()) {
            $statusTimelines[] = [
                'id' => 'seatBookingTimelineChart',
                'label' => __('Seat Booking Timeline'),
                'series_label' => __('Seat booked'),
                'color' => '#f97316',
                'background' => 'rgba(249, 115, 22, 0.1)',
                'times' => $this->participantTimestamps('seat_booked_at'),
            ];
        }

        return [
            'registrations' => $registrationsCount,
            'class_distribution' => [
                'labels' => $classData->pluck('class')->map(fn ($c) => $c ?? __('Unknown'))->toArray(),
                'data' => $classData->pluck('count')->toArray(),
                'colors' => $classData->map(fn ($item) => '#'.substr(md5($item->class ?? 'Unknown'), 0, 6))->toArray(),
            ],
            'registration_timeline' => [
                'times' => $this->participantTimestamps('created_at'),
                'event_start' => $eventStart,
                'event_created' => $eventCreated,
                'display_starts_at' => $displayStartsAt,
                'event_ends_at' => $eventEndsAt,
            ],
            'timeline_anchors' => [
                'event_start' => $eventStart,
                'display_starts_at' => $displayStartsAt,
                'event_ends_at' => $eventEndsAt,
            ],
            'status_timelines' => $statusTimelines,
            'paid_count' => $paidCount,
            'arrived_count' => $arrivedCount,
            'seat_counts' => [
                'with_seat' => $this->event->seatedParticipants()->count(),
                'without_seat' => $this->event->participants()->where('is_without_seat_place', true)->count(),
            ],
            'earnings' => [
                'entry_fee' => $entryFeeRevenue,
                'kiosk' => $kioskRevenue,
                'total' => round($entryFeeRevenue + $kioskRevenue, 2),
            ],
        ];
    }

    /**
     * The timestamps (in milliseconds) at which participants got the given
     * timestamp column set, oldest first.
     *
     * @return list<int>
     */
    private function participantTimestamps(string $column): array
    {
        return $this->event->participants()
            ->whereNotNull($column)
            ->oldest($column)
            ->pluck($column)
            ->values()
            ->map(fn ($date) => $date->getTimestamp() * 1000)
            ->toArray();
    }

    public function render()
    {
        return view('livewire.admin.events.tabs.view-details');
    }
}
