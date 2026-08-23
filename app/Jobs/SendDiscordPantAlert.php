<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\AppConfig;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;

class SendDiscordPantAlert implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $webhookUrl = config('services.discord.togethernet_webhook_url');

        if (! $webhookUrl) {
            return;
        }

        $message = "# Panten är full\n"
        ."Ta med panten till någon butik för återvinning (t.ex. [ICA](<https://maps.app.goo.gl/8mcnxtccTL7aWAzi6>) eller [Lidle](<https://maps.app.goo.gl/8XufEh63xAzaN7M98>) i Kista Centrum), ta hand om panten och skicka in dina resultat via [togethernet](https://togethernet.ssis.nu/admin/panten).\n\n"
        .'<@&'.AppConfig::get('discord_togethernet_role_id', 'unset').'>';

        Http::post($webhookUrl, [
            'content' => $message,
        ]);
    }
}
