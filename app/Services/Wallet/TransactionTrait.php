<?php

namespace App\Services\Wallet;

use App\Enum\Wallet\TransactionStatusEnum;
use Illuminate\Support\Str;
use App\Models\Wallet\Wallet;
use App\Models\Wallet\Transaction;
use Illuminate\Support\Facades\DB;

trait TransactionTrait
{
    public static function forceDeposit(
        ?int $walletId,
        int $amount,
        string $status = TransactionStatusEnum::DEPOSIT->value,
        ?string $groupId = null,
        bool $confirmed = false,
        $meta = null,
        array|null $other  = null
    )
    {
        Wallet::where('id', $walletId)
            ->update(['balance' => DB::raw("balance + {$amount}")]);
                
        $meta = $meta ? ['meta' => $meta] : [];
        $other = $other ? $other : [];
        
        Transaction::create([
            'wallet_id' => $walletId,
            'amount' => $amount,
            'status' => $status,
            'transfer_group_id' => $groupId,
            'confirmed'=> $confirmed,
            ...$meta,
            ...$other
        ]);
    }

    public static function forceDepositDb(
        ?int $walletId,
        int $amount,
        string $status = TransactionStatusEnum::DEPOSIT->value,
        ?string $groupId = null,
        bool $confirmed = false,
        int $retry = 5,
        $meta = null,
        array|null $other  = null
    )
    {
        return DB::transaction(fn () => self::forceDeposit($walletId, $amount, $status, $groupId, $confirmed, $meta, $other), $retry);
    }

    public static function forceWithdraw(
        ?int $walletId,
        int $amount,
        string $status = TransactionStatusEnum::DEPOSIT->value,
        ?string $groupId = null,
        bool $confirmed = false,
        $meta = null,
        array|null $other  = null
    )
    {
        Wallet::where('id', $walletId)
            ->update(['balance' => DB::raw("balance - {$amount}")]);
                
        $meta = $meta ? ['meta' => $meta] : [];
        $other = $other ? $other : [];

        Transaction::create([
            'wallet_id' => $walletId,
            'amount' => -$amount,
            'status' => $status,
            'transfer_group_id' => $groupId,
            'confirmed'=> $confirmed,
            ...$meta,
            ...$other
        ]);
    }

    public static function forceWithdrawDb(
        ?int $walletId,
        int $amount,
        string $status = TransactionStatusEnum::DEPOSIT->value,
        ?string $groupId = null,
        bool $confirmed = false,
        int $retry = 5,
        $meta = null,
        array|null $other  = null
    )
    {
        return DB::transaction(fn () => self::forceWithdraw($walletId, $amount, $status, $groupId, $confirmed, $meta, $other), $retry);
    }

    public static function forceTransfer(
        int $fromWalletId,
        int $toWalletId,
        string $amountMinorUnits,
        ?array $meta = null,
        int $retry = 5,
        array|null $other  = []
    )
    {
        // deterministic lock order
        [$first, $second] = $fromWalletId < $toWalletId ? [$fromWalletId, $toWalletId] : [$toWalletId, $fromWalletId];

        return DB::transaction(function () use ($fromWalletId, $toWalletId, $amountMinorUnits, $meta, $first, $second) {
            // Lock both rows
            $firstWallet = Wallet::where('id', $first)->lockForUpdate()->firstOrFail();
            $secondWallet = Wallet::where('id', $second)->lockForUpdate()->firstOrFail();


            $fromWallet = $fromWalletId === $first ? $firstWallet : $secondWallet;
            $toWallet = $toWalletId === $first ? $firstWallet : $secondWallet;

            // optional: check currency match
            if ($fromWallet->currency_id !== $toWallet->currency_id) {
                throw new \Exception('Currency mismatch');
            }

            $groupId = Str::uuid();
            $status = TransactionStatusEnum::TRANSFER->value;
            $confirmed = true;

            self::forceWithdraw(
                $fromWallet->id,
                $amountMinorUnits,
                $status,
                $groupId,
                $confirmed,
                $meta,
                other: [
                    'holder_type' => Wallet::class,
                    'holder_id' => $fromWallet->id
                ]
            );
            self::forceDeposit(
                $toWallet->id,
                $amountMinorUnits,
                $status,
                $groupId,
                $confirmed,
                $meta,
                other: [
                    'holder_type' => Wallet::class,
                    'holder_id' => $toWallet->id
                ]
            );
            
            return $groupId;
        }, $retry);
    }
}