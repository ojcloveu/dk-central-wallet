<?php

namespace App\Enum;

use Illuminate\Support\Str;

trait EnumTrait
{
    public static function keys()
    {
        return collect(self::cases())->map(fn ($v) => $v->name)->toArray();
    }

    public static function values()
    {
        return collect(self::cases())->map(fn ($v) => $v->value)->toArray();
    }

    public static function collect()
    {
        return collect(self::cases())->mapWithKeys(fn ($v) => [$v->name => $v->value]);
    }

    public static function matchDefaultStatus(string $status)
    {
        // check enum name before enum value
        $checkName = collect(self::keys())->first(fn ($v) => $v == $status);

        if (! $checkName) {
            return collect(self::values())->first(fn ($v) => $v == $status) ?: self::defaultStatus();
        }
        // php 8.3 or later
        // return PaymentStatus::{$checkName}->value
        // php 8.0 or later
        return constant("self::$checkName")->value;
    }

    public static function matchStatus(string $status, ?string $provider = null)
    {
        if ($provider && method_exists(self::class, $method = Str::camel('match '.$provider.' status'))) {
            return self::$method($status);
        }

        return self::matchDefaultStatus($status);
    }
}
