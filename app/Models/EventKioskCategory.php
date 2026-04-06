<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventKioskCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'kiosk_id'];

    public function kiosk(): BelongsTo
    {
        return $this->belongsTo(EventKiosk::class, 'kiosk_id');
    }

    public function articles(): HasMany
    {
        return $this->hasMany(EventKioskArticle::class, 'category_id');
    }
}
