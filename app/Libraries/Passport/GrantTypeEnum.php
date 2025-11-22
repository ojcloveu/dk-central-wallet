<?php

namespace App\Libraries\Passport;

use App\Enum\EnumTrait;

enum GrantTypeEnum: string
{
    use EnumTrait;

    case PASSWORD = 'password';

    case REFRESH_TOKEN = 'refresh_token';
}


