<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventKioskPurchaseItem extends Model
{
    protected $fillable = [
        'purchase_id',
        'article_id',
        'amount',
        'cost',
    ];

    protected $casts = [
        'amount' => 'integer',
        'cost' => 'integer',
    ];

    /**
     * @return BelongsTo<EventKioskPurchase, $this>
     */
    public function purchase(): BelongsTo
    {
        return $this->belongsTo(EventKioskPurchase::class, 'purchase_id');
    }

    /**
     * @return BelongsTo<EventKioskArticle, $this>
     */
    public function article(): BelongsTo
    {
        return $this->belongsTo(EventKioskArticle::class, 'article_id');
    }
}
