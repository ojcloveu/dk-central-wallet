<?php

namespace App\Enum\Wallet;

use Illuminate\Support\Facades\DB;

enum TransactionStatusEnum: string
{
    case DEPOSIT = 'deposit';
    case WITHDRAW = 'withdraw';
    case TRANSFER = 'transfer';
}
