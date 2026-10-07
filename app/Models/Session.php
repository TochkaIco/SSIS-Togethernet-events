<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\WithoutIncrementing;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Jenssegers\Agent\Agent;

#[WithoutIncrementing]
#[WithoutTimestamps]
class Session extends Model
{
    protected $keyType = 'string';

    /**
     * Parse the user agent string into a readable Agent object.
     */
    protected function agent(): Attribute
    {
        return Attribute::make(get: function () {
            return tap(new Agent, fn ($agent) => $agent->setUserAgent($this->user_agent));
        });
    }

    /**
     * Check if this session is the one the user is currently using.
     */
    protected function isCurrentDevice(): Attribute
    {
        return Attribute::make(get: function (): bool {
            return $this->id === request()->session()->getId();
        });
    }

    /**
     * Format the last activity timestamp.
     */
    protected function lastActive(): Attribute
    {
        return Attribute::make(get: function (): string {
            return Carbon::createFromTimestamp($this->last_activity)->diffForHumans();
        });
    }
}
