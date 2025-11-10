<?php

namespace App\Models\Wallet;

use App\Observers\WalletObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

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
        'create_by',
        'update_by',
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
            'create_by' => 'integer',
            'update_by' => 'integer',
            'meta' => 'object',
        ];
    }

    public function meta(): Attribute
    {
        return Attribute::make(
            set: fn () => null
        );
    }
}
