<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Jenssegers\Agent\Agent;

class Session extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    /**
     * Parse the user agent string into a readable Agent object.
     */
    public function getAgentAttribute()
    {
        return tap(new Agent, fn ($agent) => $agent->setUserAgent($this->user_agent));
    }

    /**
     * Check if this session is the one the user is currently using.
     */
    public function getIsCurrentDeviceAttribute(): bool
    {
        return $this->id === request()->session()->getId();
    }

    /**
     * Format the last activity timestamp.
     */
    public function getLastActiveAttribute(): string
    {
        return Carbon::createFromTimestamp($this->last_activity)->diffForHumans();
    }
}
