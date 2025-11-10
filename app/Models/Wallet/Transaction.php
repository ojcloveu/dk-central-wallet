<?php

namespace App\Models\Wallet;

use App\Observers\TransactionObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy(TransactionObserver::class)]
class Transaction extends Model
{
    protected $fillable = [
        'uuid',
        'wallet_id',
        'balance',
        'status',
        'confirmed',
        'remark',
        'transfer_group_id',
        'actionable_type',
        'actionable_id',
        'holder_type',
        'holder_id',
        'meta',
        'created_by',
        'updated_by'
    ];

    protected function casts(): array
    {
        return [
            'uuid' => 'string',
            'wallet_id' => 'integer',
            'balance' => 'integer',
            'status' => 'string',
            'confirmed' => 'boolean',
            'remark' => 'string',
            'transfer_group_id' => 'string',
            'actionable_type' => 'string',
            'actionable_id' => 'integer',
            'holder_type' => 'string',
            'holder_id' => 'integer',
            'meta' => 'array',
            'created_by' => 'integer',
            'updated_by' => 'integer'
        ];
    }
}
