<?php

declare(strict_types=1);

namespace App;

enum FeedbackType: string
{
    case BUG = 'bug';
    case FEATURE = 'feature';
    case QOL = 'qol';

    public function label(): string
    {
        return match ($this) {
            self::BUG => __('Bug'),
            self::FEATURE => __('Feature'),
            self::QOL => __('QOL'),
        };
    }
}
