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

    public static function discord_log(string $title, string $type, array $details = []): void
    {
        $appUrl = config('app.url') ?? '';

        $pairs = array_map(
            fn($chunk) => implode(' => ', $chunk),
            array_chunk($details, 2)
        );
        $strDetails = Arr::join($pairs, ', ', ' and ');

        $message = "**Log from [togethernet.ssis.nu]({$appUrl})**
__Title__: ``{$title}``
__Type__: ``{$type}``
__Details__: ``{$strDetails}``";
        SendDiscordLog::dispatch($message);
    }

    protected function casts(): array
    {
        return [
            'details' => 'array',
        ];
    }
}
