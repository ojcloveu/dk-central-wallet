<?php

namespace App\Enum;

use App\Enum\EnumTrait;
use App\Models\CommissionScheduleLog;
use Illuminate\Support\Str;

enum OnQueueEnum: string
{
    use EnumTrait;

    // php artisan queue:work --queue=high,default,low,trx_high,trx_default,trx_low,wh_high,wh_default,wh_low

    // TRANSACTIONS
    case TRANSACTION_HIGH = 'trx_high';
    case TRANSACTION_DEFAULT = 'trx_default';
    case TRANSACTION_LOW = 'trx_low';

    // WEBHOOKS
    case WEBHOOK_HIGH = 'wh_high';
    case WEBHOOK_DEFAULT = 'wh_default';
    case WEBHOOK_LOW = 'wh_low';
}
