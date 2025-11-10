<?php

namespace App\Models\Wallet;

use App\Libraries\UniqueId;
use Illuminate\Support\Str;
use App\Observers\WalletObserver;
use App\Models\Wallet\Transaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy(WalletObserver::class)]
class Wallet extends Model
{
    protected $fillable = [
        'uuid',
        'user_id',
        'currency_id',
        'balance',
        'name',
        'type',
        'created_by',
        'updated_by',
        'meta',

    ];

    protected function casts(): array
    {
        return [
            'uuid' => 'string',
            'user_id' => 'integer',
            'currency_id' => 'integer',
            'balance' => 'integer',
            'name' => 'string',
            'type' => 'string',
            'created_by' => 'integer',
            'updated_by' => 'integer',
            'meta' => 'array',
        ];
    }

    // RELATIONSHIPS
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function confirmTransactions()
    {
        return $this->hasMany(Transaction::class)->where('confirmed', 1);
    }

    public function notConfirmTransactions()
    {
        return $this->hasMany(Transaction::class)->where('confirmed', 0);
    }

    // SCOPES
    #[Scope]
    protected function findAccountNumber($query, $value, $enableLike = false): void
    {
        $value = Str::replace(' ', '', $value);

        if ($enableLike) {
            $query->where('uuid', 'like', $value.'%');
        } else {
            $query->where('uuid', $value);
        }
    }

    // ACCESSOR / MUTATOR
    public function meta(): Attribute
    {
        return Attribute::make(
            set: fn () => null
        );
    }

    public function accountNumber(): Attribute
    {
        return Attribute::make(
            get: fn () => UniqueId::bankAccountSplit($this->uuid)
        );
    }
}
