<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\GlobalLogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'action_title',
    'action_type',
    'details',
    'user_id',
])]
class GlobalLog extends Model
{
    /** @use HasFactory<GlobalLogFactory> */
    use HasFactory;

    protected $casts = [
        'details' => 'array',
    ];

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
}
