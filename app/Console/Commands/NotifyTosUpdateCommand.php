<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Mail\NewTermsMail;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class NotifyTosUpdateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:notify-tos-update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notify users about the new Terms of Service and anonymize those who do not accept after 1 month.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->sendNotifications();
        $this->anonymizeUnaccepted();
    }

    private function sendNotifications(): void
    {
        $usersToNotify = User::notAnonymized()
            ->whereNull('tos_accepted_at')
            ->whereNull('tos_warning_sent_at')
            ->get();

        $this->info("Sending TOS update notifications to {$usersToNotify->count()} users...");

        foreach ($usersToNotify as $user) {
            Mail::to($user->email)->send(new NewTermsMail($user));
            $user->update(['tos_warning_sent_at' => now()]);
        }
    }

    private function anonymizeUnaccepted(): void
    {
        $anonymizeThreshold = now()->subMonth();

        $usersToAnonymize = User::notAnonymized()
            ->whereNull('tos_accepted_at')
            ->whereNotNull('tos_warning_sent_at')
            ->where('tos_warning_sent_at', '<', $anonymizeThreshold)
            ->get();

        if ($usersToAnonymize->isEmpty()) {
            return;
        }

        $this->info("Anonymizing {$usersToAnonymize->count()} users who did not accept TOS in time...");

        foreach ($usersToAnonymize as $user) {
            $user->anonymize();
        }
    }
}
