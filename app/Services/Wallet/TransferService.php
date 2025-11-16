<?php

namespace App\Services\Wallet;

use App\Models\User;
use Illuminate\Support\Str;
use App\Models\Wallet\Wallet;
use App\Models\Wallet\Transaction;
use Illuminate\Support\Facades\DB;
use App\Services\Wallet\Traits\DbTrait;
use App\Services\Wallet\Traits\SetTrait;
use App\Enum\Wallet\TransactionStatusEnum;
use App\Services\Wallet\Traits\ExecuteTrait;
use App\Services\Wallet\Traits\ValidateTrait;
use Illuminate\Validation\ValidationException;

class TransferService
{
    use SetTrait;
    use DbTrait;
    use ValidateTrait;
    use ExecuteTrait;

    protected string $status = TransactionStatusEnum::WITHDRAW->value;
    protected object $from;
    protected object $to;

    public function __construct(
        protected bool $dbTransaction,
        int $retry
    )
    {
        $this->retry = $retry;
    }

    public static function dbTransaction($enable = true, $retry = 100): static
    {
        return new static($enable, $retry);
    }

    public function from($from): static
    {
        $this->from = is_object($from)
            ? $from
            : Wallet::FindAccountNumber($from)
                ->orWhere('id', $from)->first();

        if (! $this->from) {
            throw new \UnexpectedValueException('Invalid From Wallet.');
        }

        return $this;
    }

    public function to($to): static
    {
        $this->to = is_object($to)
            ? $to
            : Wallet::FindAccountNumber($to)
                ->orWhere('id', $to)->first();

        if (! $this->to) {
            throw new \UnexpectedValueException('Invalid To Wallet.');
        }

        return $this;
    }

    protected function createWithoutDb()
    {
        $amountFrom = WalletService::amountToDb($this->amount, $this->from->metaMinorUnit);
        $amountTo = WalletService::amountToDb($this->amount, $this->to->metaMinorUnit);

        if ($this->from->currency_id !== $this->to->currency_id) {
            throw ValidationException::withMessages([
                'amount' => ['Currency mismatch.']
            ]);
        }

        if (! $this->forced) {
            $this->validated($amountFrom, $this->from->id);
        }

        $meta = $this->meta
            ? ['meta' => [...$this->from->meta, ...$this->meta]]
            : ['meta' => $this->from->meta];
        
        $groupId = Str::uuid();
        
        // from
        Transaction::create([
            'wallet_id' => $this->from->id,
            'balance' => -$amountFrom,
            'status' => $this->status,
            'confirmed'=> $this->confirmed,
            'holder_id' => $this->from->user_id,
            'holder_type' => User::class,
            'transfer_group_id' => $groupId,
            ...$meta,
            ...$this->otherAttribute
        ]);
        
        // to
        Transaction::create([
            'wallet_id' => $this->to->id,
            'balance' => $amountTo,
            'status' => $this->status,
            'confirmed'=> $this->confirmed,
            'holder_id' => $this->to->user_id,
            'holder_type' => User::class,
            'transfer_group_id' => $groupId,
            ...$meta,
            ...$this->otherAttribute
        ]);

        if ($this->confirmed) {
            $this->updateWallet(
                $this->from->id,
                $this->to->id,
                $amountFrom,
                $amountTo
            );
        }

        return $groupId;
    }

    protected function updateWallet($fromId, $toId, $amountFrom, $amountTo)
    {
        Wallet::where('id', $fromId)
            ->update(['balance' => DB::raw("balance - {$amountFrom}")]);

        Wallet::where('id', $toId)
            ->update(['balance' => DB::raw("balance + {$amountTo}")]);
    }

    protected function updateWithoutDb()
    {
        $fromTrans = null;
        $toTrans = null;

        if ($this->transaction->count() == 2) {
            foreach($this->transaction as $trans) {
                if ($trans->balance < 0) {
                    $fromTrans = $trans;
                } else {
                    $toTrans = $trans;
                }
            }
        } else if ($this->transaction->count() > 0) {
            throw new \UnexpectedValueException('Unexpected data corrupted.');
        }

        $metaFrom = $this->meta
            ? ['meta' => [...$fromTrans->meta, ...$this->meta]]
            : [];

        $metaTo = $this->meta
            ? ['meta' => [...$toTrans->meta, ...$this->meta]]
            : [];

        Transaction::where('uuid', $fromTrans->uuid)
            ->update([
                'confirmed' => true,
                ...$metaFrom,
                ...$this->otherAttribute
            ]);

        Transaction::where('uuid', $toTrans->uuid)
        ->update([
            'confirmed' => true,
            ...$metaTo,
            ...$this->otherAttribute
        ]);

            
        $this->updateWallet(
            $fromTrans->wallet_id,
            $toTrans->wallet_id,
            abs($fromTrans->balance),
            $toTrans->balance,
        );

        return $this->transaction->fresh();
    }

    // OVERRIDE TRAIT METHOD
    public function transaction($transaction): static
    {
        $this->transaction = is_object($transaction)
            ? $transaction
            : Transaction::where('transfer_group_id', $transaction)->get();

        if (! $this->transaction) {
            throw new \UnexpectedValueException('Invalid Transaction..');
        }

        return $this;
    }
}