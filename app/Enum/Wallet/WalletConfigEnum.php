<?php
namespace App\Enum\Wallet;

use App\Enum\EnumTrait;

enum WalletConfigEnum: string
{
    use EnumTrait;
    
    case DEFAULT_TYPE = WalletTypeEnum::DEFAULT->value;

    public static function defaultCurrencyId()
    {
        return 1;
    }

    public static function defaultType()
    {
        return self::DEFAULT_TYPE->value;
    }
}