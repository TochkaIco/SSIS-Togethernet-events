<?php

declare(strict_types=1);

namespace App;

enum EventType: string
{
    case KARAOKE = 'karaoke';
    case FILM_PARTY = 'film_party';
    case CUSTOM = 'custom';

    public function label(): string
    {
        return match ($this) {
            self::KARAOKE => __('Karaoke'),
            self::FILM_PARTY => __('Film Party'),
            self::CUSTOM => __('Custom'),
        };
    }
}
