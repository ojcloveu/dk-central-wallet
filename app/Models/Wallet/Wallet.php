<?php

namespace App\Models\Wallet;

use App\Observers\WalletObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy(WalletObserver::class)]
class Wallet extends Model
{
    //
}
