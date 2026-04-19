<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\EventUserFactory;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property int $id
 * @property int $user_id
 * @property int $event_id
 * @property bool $in_waitinglist
 * @property bool $has_paid
 * @property bool $has_arrived
 * @property bool $is_working
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
#[Table(name: 'event_users')]
class EventUser extends Pivot
{
    /** @use HasFactory<EventUserFactory> */
    use HasFactory;

    public $incrementing = true;

    protected function casts(): array
    {
        return [
            'in_waitinglist' => 'boolean',
            'has_paid' => 'boolean',
            'has_arrived' => 'boolean',
            'event_id' => 'integer',
            'user_id' => 'integer',
            'is_working' => 'boolean',
            'period' => 'integer',
        ];
    }
}
