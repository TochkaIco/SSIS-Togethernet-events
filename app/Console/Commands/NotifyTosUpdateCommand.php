<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Mail\NewTermsMail;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

#[Description('Notify users about the new Terms of Service and anonymize those who do not accept after 1 month.')]
#[Signature('app:notify-tos-update')]
class NotifyTosUpdateCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->sendNotifications();
        $this->removeUnaccepted();
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

    private function removeUnaccepted(): void
    {
        $removeThreshold = now()->subMonth();

        $usersToRemove = User::notAnonymized()
            ->whereNull('tos_accepted_at')
            ->whereNotNull('tos_warning_sent_at')
            ->where('tos_warning_sent_at', '<', $removeThreshold)
            ->get();

        if ($usersToRemove->isEmpty()) {
            return;
        }

        $this->info("Removing {$usersToRemove->count()} users who did not accept TOS in time...");

        foreach ($usersToRemove as $user) {
            $user->remove();
        }
    }
}
