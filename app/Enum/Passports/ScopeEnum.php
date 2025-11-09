<?php

namespace App\Enum\Passports;

use App\Enum\EnumTrait;
use Illuminate\Support\Facades\DB;

enum ScopeEnum: string
{
    use EnumTrait;

    case USER = 'user';

    case OAUTH = 'oauth';

    public static function can()
    {
        return collect(self::values())->mapWithKeys(fn ($v) => [$v => $v])->toArray();
    }

    public static function routeScope(string|array $scopes): string
    {
        return 'scope:'.static::implode($scopes);
    }

    public static function routeScopes(string|array $scopes): string
    {
        return 'scopes:'.static::implode($scopes);
    }

    protected static function implode(string|array $data, $separate = ',')
    {
        return collect((array) $data)->filter()->implode($separate);
    }

    public static function routeClient(string|array $scopes): string
    {
        return 'client:'.static::implode($scopes);
    }
}
