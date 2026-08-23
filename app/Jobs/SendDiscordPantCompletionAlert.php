<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\PantAlert;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;

class SendDiscordPantCompletionAlert implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public PantAlert $alert)
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

        $names = $this->alert->completedByUsers->pluck('name')->implode(', ');

        $message = sprintf(
            "# Panten är återvunnen!\nStort tack till %s som slutförde pantningen.\nTotalt samlades det in %s kr.",
            $names ?: 'okända medlemmar',
            number_format((float) ($this->alert->sek_received ?? 0.0), 2, ',', ' ')
        );

        Http::post($webhookUrl, [
            'content' => $message,
        ]);
    }
}
