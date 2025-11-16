<?php

namespace App\Services\Wallet;

use App\Models\User;
use App\Models\Wallet\Wallet;
use App\Models\Wallet\Transaction;
use Illuminate\Support\Facades\DB;
use App\Services\Wallet\Traits\DbTrait;
use App\Enum\Wallet\TransactionStatusEnum;
use App\Services\Wallet\Traits\ExecuteTrait;
use App\Services\Wallet\Traits\SetTrait;

class DepositService
{
    use SetTrait;
    use DbTrait;
    use ExecuteTrait;

    protected string $status = TransactionStatusEnum::DEPOSIT->value;

    public function __construct(
        protected bool $dbTransaction,
        int $retry
    )
    {
        $this->retry = $retry;
    }

    public static function dbTransaction($enable = true, $retry = 5): static
    {
        return new static($enable, $retry);
    }

    protected function createWithoutDb()
    {
        $amount = WalletService::amountToDb($this->amount, $this->wallet->metaMinorUnit);

        if ($this->confirmed) {
            $this->updateWallet(
                $this->wallet->id,
                $amount
            );
        }
                
        $meta = $this->meta
            ? ['meta' => [...$this->wallet->meta, ...$this->meta]]
            : ['meta' => $this->wallet->meta];
        
        return Transaction::create([
            'wallet_id' => $this->wallet->id,
            'balance' => $amount,
            'status' => $this->status,
            'confirmed'=> $this->confirmed,
            'holder_id' => $this->wallet->user_id,
            'holder_type' => User::class,
            ...$meta,
            ...$this->otherAttribute
        ]);
    }

    protected function updateWallet($walletId, $amount)
    {
        Wallet::where('id', $walletId)
            ->update(['balance' => DB::raw("balance + {$amount}")]);
    }

    protected function updateWithoutDb()
    {
        if (! $this->transaction->confirmed) {
            $this->updateWallet(
                $this->transaction->wallet_id,
                $this->transaction->balance
            );

            $meta = $this->meta
                ? ['meta' => [...$this->transaction->meta, ...$this->meta]]
                : [];

            return Transaction::where('uuid', $this->transaction->uuid)
                ->update([
                    'confirmed' => true,
                    ...$meta,
                    ...$this->otherAttribute
                ]);
        }

        return false;
    }
}