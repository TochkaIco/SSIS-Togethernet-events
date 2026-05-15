<?php

declare(strict_types=1);

namespace App;

enum EventType: string
{
    case KARAOKE = 'karaoke';
    case FILM_PARTY = 'film_party';
    case QR_TAG = 'qr_tag';
    case CUSTOM = 'custom';

    public function label(): string
    {
        return match ($this) {
            self::KARAOKE => __('Karaoke'),
            self::FILM_PARTY => __('Film Party'),
            self::QR_TAG => __('QR-Tag'),
            self::CUSTOM => __('Custom'),
        };
    }
}
