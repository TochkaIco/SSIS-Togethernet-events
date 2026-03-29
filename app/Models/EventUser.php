<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\EventUserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['in_waitinglist', 'has_payed', 'has_arrived', 'event_id', 'user_id'])]
class EventUser extends Model
{
    /** @use HasFactory<EventUserFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'in_waitinglist' => 'boolean',
            'has_payed' => 'boolean',
            'has_arrived' => 'boolean',
            'event_id' => 'integer',
            'user_id' => 'integer',
        ];
    }
}
