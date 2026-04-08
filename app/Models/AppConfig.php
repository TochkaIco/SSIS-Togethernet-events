<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['key', 'value', 'type'])]
class AppConfig extends Model
{
    public static function get(string $key, $default = null)
    {
        /** @var self|null $config */
        $config = self::firstOrCreate(
            ['key' => $key],
            ['value' => (string) $default, 'type' => gettype($default)]
        );

        if (! $config) {
            return $default;
        }

        return match ($config->type) {
            'boolean' => filter_var($config->value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $config->value,
            default => $config->value,
        };
    }
}
