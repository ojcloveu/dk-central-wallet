<?php
namespace App\Enum;

use App\Enum\EnumTrait;

enum WalletTypeEnum: string
{
    use EnumTrait;
    
    case DEFAULT = 'default';

}