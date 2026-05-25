<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\GlobalLog;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Description('Reset TOS acceptance for all users to force them to review updated terms.')]
#[Signature('app:reset-tos')]
class ResetTosAcceptanceCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        if (! $this->confirm('This will force all users to accept the Terms of Service again. Do you want to continue?')) {
            return;
        }

        $count = User::notAnonymized()->update([
            'tos_accepted_at' => null,
            'tos_warning_sent_at' => null,
        ]);

        $this->info("Successfully reset TOS status for {$count} users.");
        $this->info('Users will be notified by the scheduled notify-tos-update command and prompted upon login.');
        GlobalLog::log('TOS has been updated, users will be notified soon', 'system');
    }
}
