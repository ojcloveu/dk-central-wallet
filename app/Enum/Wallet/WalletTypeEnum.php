<?php
namespace App\Enum\Wallet;

use App\Enum\EnumTrait;

enum WalletTypeEnum: string
{
    use EnumTrait;
    
    case DEFAULT = 'default';

}