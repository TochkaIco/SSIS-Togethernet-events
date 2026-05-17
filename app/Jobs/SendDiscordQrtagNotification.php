<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class SendDiscordQrtagNotification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public string $message)
    {
        //
    }

    /**
     * Execute the job.
     *
     * @throws ConnectionException
     */
    public function handle(): void
    {
        $webhookUrl = config('services.discord.webhook_url');

        if (! $webhookUrl) {
            return;
        }

        Http::post($webhookUrl, [
            'content' => $this->message,
        ]);
    }
}
