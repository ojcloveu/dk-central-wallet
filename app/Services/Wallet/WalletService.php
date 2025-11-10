<?php

namespace App\Services\Wallet;

use App\Models\Wallet\Wallet;
use App\Models\Wallet\Currency;
use App\Enum\Wallet\WalletTypeEnum;

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
            'currency_id' => $currency->id,
            'name' => 'Wallet '.$currency->name,
            'type' => $type
        ]);
    }
}
