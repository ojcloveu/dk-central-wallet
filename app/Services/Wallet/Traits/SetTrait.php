<?php

namespace App\Services\Wallet\Traits;

use App\Models\Wallet\Wallet;
use App\Models\Wallet\Transaction;

trait SetTrait
{
    protected int|float $amount;
    protected bool $confirmed = false;
    protected array|string $meta = [];
    protected array $otherAttribute = [];
    protected object $wallet;
    protected object $transaction;

    public function confirmed(): static
    {
        $this->confirmed = true;

        return $this;
    }

    public function status(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function amount(int|float|string $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function meta(string|array $meta): static
    {
        if (is_string($meta)) {
            $meta = json_decode($meta, true);

            if (! $meta) {
                throw new \UnexpectedValueException('Invalid meta.');
            }
        }

        $this->meta = $meta;

        return $this;
    }

    public function otherAttribute(array $data): static
    {
        $this->otherAttribute = $data;

        return $this;
    }

    public function wallet(int|string|object $wallet):static
    {
        $this->wallet = is_object($wallet)
            ? $wallet
            : Wallet::FindAccountNumber($wallet)
                ->orWhere('id', $wallet)->first();

        if (! $this->wallet) {
            throw new \UnexpectedValueException('Invalid Wallet.');
        }

        return $this;
    }

    public function transaction($transaction): static
    {
        $this->transaction = is_object($transaction)
            ? $transaction
            : Transaction::where('uuid', $transaction)->first();

        if (! $this->transaction) {
            throw new \UnexpectedValueException('Invalid Transaction..');
        }

        return $this;
    }
}