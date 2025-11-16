<?php

namespace App\Services\Wallet\Traits;

use App\Models\Wallet\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

trait ValidateTrait
{
    protected bool $forced = false;

    /**
     * Enable validate: Prevent withdraw amount below 0
     */
    public function forced(): static
    {
        $this->forced = true;

        return $this;
    }

    protected function validated($amount, $walletId)
    {
        $select = Transaction::selectRaw('
            SUM(CASE WHEN confirmed = 1 THEN balance ELSE 0 END) AS sum_confirmed,
            SUM(CASE WHEN confirmed = 0 AND balance < 0 THEN balance ELSE 0 END) AS sum_unconfirmed
        ')
            ->where('wallet_id', $walletId)
            ->first();

        $blockAmount = $amount + abs($select->sum_unconfirmed);

        // dd($amount, $blockAmount, $select->sum_confirmed, $select->sum_confirmed - $blockAmount);

        if ($select->sum_confirmed - $blockAmount < 0) {
            throw ValidationException::withMessages([
                'amount' => ['Your balance is not sufficient.']
            ]);
        }
    }
}