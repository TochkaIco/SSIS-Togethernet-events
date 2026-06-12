<?php

declare(strict_types=1);

use App\Livewire\Admin\Events\Tabs\ViewDetails;
use App\Models\Event;
use App\Models\EventUser;
use App\Models\User;
use Livewire\Livewire;

it('calculates statistics correctly', function () {
    $event = Event::factory()->create(['num_of_seats' => 10]);

    $user1 = User::factory()->create(['class' => 'TE22']);
    $user2 = User::factory()->create(['class' => 'TE22']);
    $user3 = User::factory()->create(['class' => 'EE23']);

    EventUser::factory()->create([
        'event_id' => $event->id,
        'user_id' => $user1->id,
        'has_arrived' => true,
        'in_waitinglist' => false,
    ]);

    EventUser::factory()->create([
        'event_id' => $event->id,
        'user_id' => $user2->id,
        'has_arrived' => false,
        'in_waitinglist' => false,
    ]);

    EventUser::factory()->create([
        'event_id' => $event->id,
        'user_id' => $user3->id,
        'has_arrived' => true,
        'in_waitinglist' => false,
    ]);

    // Waiting list user - should not be counted in stats
    EventUser::factory()->create([
        'event_id' => $event->id,
        'user_id' => User::factory()->create()->id,
        'in_waitinglist' => true,
    ]);

    $component = Livewire::test(ViewDetails::class, ['event' => $event]);

    $stats = $component->get('stats');

    expect($stats['registrations'])->toBe(3);
    expect($stats['attendance'])->toBe(2);
    expect($stats['attendance_rate'])->toBe(67); // round(2/3 * 100)

    $classDist = $stats['class_distribution'];
    expect($classDist['labels'])->toContain('TE22', 'EE23');
    expect($classDist['data'])->toContain(2, 1);
});
