<?php

namespace Database\Seeders;

use App\EventType;
use App\Models\Event;
use App\Models\EventUser;
use App\Models\Feedback;
use App\Models\QrTagLog;
use App\Models\User;
use Illuminate\Database\Seeder;

class DevSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create specific users for easy testing
        $superAdmin = User::updateOrCreate(
            ['email' => 'superadmin@stockholmscience.se'],
            [
                'name' => 'Super Admin',
                'class' => 'Staff',
                'google_id' => '1',
                'email_verified_at' => now(),
                'tos_accepted_at' => now(),
            ]
        );
        $superAdmin->assignRole('super-admin');

        $admin = User::updateOrCreate(
            ['email' => 'admin@stockholmscience.se'],
            [
                'name' => 'Admin User',
                'class' => 'Staff',
                'google_id' => '2',
                'email_verified_at' => now(),
                'tos_accepted_at' => now(),
            ]
        );
        $admin->assignRole('admin');

        $member = User::updateOrCreate(
            ['email' => 'member@stockholmscience.se'],
            [
                'name' => 'Tog Member',
                'class' => 'TE24A',
                'google_id' => '3',
                'email_verified_at' => now(),
                'tos_accepted_at' => now(),
            ]
        );
        $member->assignRole('tog-member');

        // 2. Create more users
        $users = User::factory(20)->create();
        foreach ($users as $user) {
            $user->assignRole('tog-member');
        }

        $allUsers = $users->push($superAdmin, $admin, $member);

        // 3. Create Events

        // Ongoing QR-Tag event
        $qrTagEvent = Event::factory()->create([
            'title' => 'Togethernet QR-Tag 2026',
            'event_type' => EventType::QR_TAG,
            'event_starts_at' => now()->subDays(2),
            'event_ends_at' => now()->addDays(5),
            'display_starts_at' => now()->subDays(10),
        ]);

        // Upcoming Karaoke event
        $karaokeEvent = Event::factory()->create([
            'title' => 'Summer Karaoke Night',
            'description' => 'Come and sing your heart out!',
            'event_type' => EventType::KARAOKE,
            'event_starts_at' => now()->addDays(10),
            'event_ends_at' => now()->addDays(10)->addHours(4),
            'display_starts_at' => now(),
            'one_hour_periods' => true,
            'one_hour_periods_number' => 4,
        ]);

        // Finished Film Party event
        $filmEvent = Event::factory()->create([
            'title' => 'Nostalgia Film Night',
            'description' => 'We watched old classics.',
            'event_type' => EventType::FILM_PARTY,
            'event_starts_at' => now()->subDays(20),
            'event_ends_at' => now()->subDays(20)->addHours(3),
            'display_starts_at' => now()->subDays(30),
        ]);

        // Upcoming Custom event
        $customEvent = Event::factory()->create([
            'title' => 'Custom Event',
            'description' => 'This is a custom event.',
            'event_type' => EventType::CUSTOM,
            'event_starts_at' => now()->addMonths(1),
            'event_ends_at' => now()->addMonths(1)->addHours(2),
        ]);

        // 4. Register users to events

        // Register everyone to QR-Tag
        foreach ($allUsers as $user) {
            EventUser::factory()->create([
                'event_id' => $qrTagEvent->id,
                'user_id' => $user->id,
                'qr_tag_token' => bin2hex(random_bytes(16)),
                'qr_tag_count' => rand(0, 5),
            ]);
        }

        // Register some to Karaoke
        foreach ($allUsers->random(10) as $user) {
            $period = $karaokeEvent->periods->random();
            EventUser::factory()->create([
                'event_id' => $karaokeEvent->id,
                'user_id' => $user->id,
                'event_period_id' => $period->id,
            ]);
        }

        // 5. Create Feedback
        Feedback::factory(10)->create([
            'user_id' => fn () => $allUsers->random()->id,
        ]);

        // 6. Create QR-Tag logs
        QrTagLog::factory()->started()->create([
            'event_id' => $qrTagEvent->id,
            'admin_id' => $superAdmin->id,
            'created_at' => now()->subMinutes(10),
        ]);

        for ($i = 0; $i < 15; $i++) {
            $u1 = $allUsers->random();
            $u2 = $allUsers->where('id', '!=', $u1->id)->random();

            QrTagLog::factory()->tagged()->create([
                'event_id' => $qrTagEvent->id,
                'user_id' => $u1->id,
                'target_user_id' => $u2->id,
            ]);
        }
    }
}
