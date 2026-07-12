<?php

declare(strict_types=1);

namespace App\Models;

use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\Fill;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['swish_number'])]
class EventKiosk extends Model
{
    /**
     * @return BelongsTo<Event, $this>
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * @return HasMany<EventKioskCategory, $this>
     */
    public function categories(): HasMany
    {
        return $this->hasMany(EventKioskCategory::class, 'kiosk_id');
    }

    /**
     * @return HasMany<EventKioskArticle, $this>
     */
    public function articles(): HasMany
    {
        return $this->hasMany(EventKioskArticle::class, 'kiosk_id');
    }

    /**
     * @return HasMany<EventKioskPurchase, $this>
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(EventKioskPurchase::class, 'kiosk_id');
    }

    public function getSwishQrCode($amount): string
    {
        $message = 'Kiosk purchase for '.$this->event->title;

        $queryParams = [
            'sw' => (string) $this->swish_number,
            'amt' => number_format((float) $amount, 2, '.', ''),
            'msg' => $message,
        ];

        $swishUrl = 'https://app.swish.nu/1/p/sw/?'.http_build_query($queryParams);

        $svg = new Writer(
            new ImageRenderer(
                new RendererStyle(300, 1, null, null, Fill::uniformColor(new Rgb(255, 255, 255), new Rgb(0, 0, 0))),
                new SvgImageBackEnd
            )
        )->writeString($swishUrl);

        $cleanSvgString = trim(substr($svg, strpos($svg, "\n") + 1));

        return 'data:image/svg+xml;base64,'.base64_encode($cleanSvgString);
    }
}
