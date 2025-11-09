<?php

namespace App\Services\Wallet;

use App\Enum\WalletTypeEnum;
use App\Models\Wallet\Currency;
use App\Models\Wallet\Wallet;

class WalletService {
    use TransactionTrait;

    public static function createWallet(
        int|object $idOrObject,
        int $currency = 1,
        string $type = WalletTypeEnum::DEFAULT->value
    )
    {
        $id = ! is_numeric($idOrObject)
            ? $idOrObject->id
            : $idOrObject;

        $currency = Currency::find($currency);

        Wallet::create([
            'user_id' => $id,
            'currency' => $currency->id,
            'name' => 'Wallet '.$currency->name,
            'type' => $type
        ]);
    }
}
