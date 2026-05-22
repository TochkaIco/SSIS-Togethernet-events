<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Mail\InactivityWarningMail;
use App\Models\GlobalLog;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class AnonymizeUsersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:anonymize-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Anonymize graduated Stockholm Science users in July and inactive other users after 7 months.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->handleGraduatedUsers();
        $this->handleInactiveUsers();
    }

    private function handleGraduatedUsers(): void
    {
        $this->info('Checking for graduated SSIS users...');

        $graduatedUsers = User::notAnonymized()
            ->where('email', 'like', '%@stockholmscience%')
            ->get()
            ->filter(fn (User $user) => ! in_array($user->class, $user->validClasses()));

        $count = 0;
        foreach ($graduatedUsers as $user) {
            $user->anonymize();
            $count++;
        }

        $this->info("Anonymized {$count} graduated users.");
        GlobalLog::log('Anonymized graduated users', 'system', ['number of users:' => $count]);
    }

    private function handleInactiveUsers(): void
    {
        $this->info('Checking for inactive users with other email domains...');

        $inactiveThreshold = now()->subMonths(7);
        $warningThreshold = now()->subMonths(6);

        $otherUsers = User::notAnonymized()
            ->where('email', 'not like', '%@stockholmscience%')
            ->get();

        $anonymizedCount = 0;
        $warningCount = 0;

        foreach ($otherUsers as $user) {
            $lastActivity = $user->last_activity_at ?? $user->created_at;

            if ($lastActivity->lt($inactiveThreshold)) {
                $user->anonymize();
                $anonymizedCount++;
                GlobalLog::log('Anonymized an inactive user', 'system', ['user_id' => $user->id]);
            } elseif ($lastActivity->lt($warningThreshold) && $user->inactivity_warning_sent_at === null) {
                Mail::to($user->email)->send(new InactivityWarningMail($user));
                $user->update(['inactivity_warning_sent_at' => now()]);
                $warningCount++;
                GlobalLog::log('Inactivity warning sent to a user', 'system', ['user_id' => $user->id]);
            }
        }

        $this->info("Anonymized {$anonymizedCount} inactive users.");
        $this->info("Sent inactivity warnings to {$warningCount} users.");
    }
}
