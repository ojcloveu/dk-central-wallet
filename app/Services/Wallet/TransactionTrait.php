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
        bool $confirmed = false
    )
    {
        Wallet::where('id', $walletId)
            ->update(['balance' => DB::raw("balance + {$amount}")]);
                
        Transaction::create([
            'wallet_id' => $walletId,
            'amount' => $amount,
            'status' => $status,
            'transfer_group_id' => $groupId,
            'confirmed'=> $confirmed
        ]);
    }

    public static function forceDepositDb(
        ?int $walletId,
        int $amount,
        string $status = TransactionStatusEnum::DEPOSIT->value,
        ?string $groupId = null,
        bool $confirmed = false,
        int $retry = 5
    )
    {
        return DB::transaction(fn () => self::forceDeposit($walletId, $amount, $status, $groupId, $confirmed), $retry);
    }

    public static function forceWithdraw(
        ?int $walletId,
        int $amount,
        string $status = TransactionStatusEnum::DEPOSIT->value,
        ?string $groupId = null,
        bool $confirmed = false
    )
    {
        Wallet::where('id', $walletId)
            ->update(['balance' => DB::raw("balance - {$amount}")]);

        Transaction::create([
            'wallet_id' => $walletId,
            'amount' => -$amount,
            'status' => $status,
            'transfer_group_id' => $groupId,
            'confirmed'=> $confirmed
        ]);
    }

    public static function forceWithdrawDb(
        ?int $walletId,
        int $amount,
        string $status = TransactionStatusEnum::DEPOSIT->value,
        ?string $groupId = null,
        bool $confirmed = false,
        int $retry = 5
    )
    {
        return DB::transaction(fn () => self::forceWithdraw($walletId, $amount, $status, $groupId, $confirmed), $retry);
    }

    public static function forceTransfer(
        int $fromWalletId,
        int $toWalletId,
        string $amountMinorUnits,
        ?array $meta = null,
        int $retry = 5
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
                $confirmed
            );
            self::forceDeposit(
                $toWallet->id,
                $amountMinorUnits,
                $status,
                $groupId,
                $confirmed
            );
            
            return $groupId;
        }, $retry);
    }
}