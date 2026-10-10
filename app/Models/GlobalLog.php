<?php

declare(strict_types=1);

namespace App\Models;

use App\Jobs\SendDiscordLog;
use Database\Factories\GlobalLogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

#[Fillable([
    'action_title',
    'action_type',
    'details',
    'user_id',
])]
/**
 * @property array<string,mixed> $details
 */
class GlobalLog extends Model
{
    /** @use HasFactory<GlobalLogFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function log(string $title, string $type, array $details = []): void
    {
        self::create([
            'action_title' => $title,
            'action_type' => $type,
            'details' => $details,
            'user_id' => auth()->id(),
        ]);
    }

    public static function discord_log(string $color, string $title, string $type, array $details = []): void
    {
        if (! filled(config('services.discord.logs_webhook_url'))) {
            return;
        }

        $appUrl = config('app.url') ?? '';

        $colorSet = [
            'red' => 16727598,
            'orange' => 16746542,
            'yellow' => 16769554,
            'blue' => 983274,
            'green' => 1232664,
        ];

        if (! $color) {
            $color = 'blue';
        }

        $pairs = array_map(
            fn ($chunk) => implode(' => ', $chunk),
            array_chunk($details, 2)
        );
        $strDetails = Arr::join($pairs, ', ');

        $message = [
            'embeds' => [
                [
                    'title' => $title,
                    'color' => $colorSet[$color],
                    'timestamp' => now()->toIso8601String(),
                    'fields' => [
                        [
                            'name' => 'Source',
                            'value' => "[Log from togethernet.ssis.nu]({$appUrl})",
                            'inline' => true,
                        ],
                        [
                            'name' => 'Type',
                            'value' => Str::ucfirst($type),
                            'inline' => true,
                        ],
                        [
                            'name' => 'Details',
                            'value' => $strDetails,
                            'inline' => false,
                        ],
                    ],
                ],
            ],
        ];

        SendDiscordLog::dispatch($message);
    }

    protected function casts(): array
    {
        return [
            'details' => 'array',
        ];
    }
}
