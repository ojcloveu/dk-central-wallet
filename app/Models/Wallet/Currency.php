<?php

namespace App\Models\Wallet;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $fillable = [
        'code',
        'short_code',
        'name',
        'minor_unit',
        'decimal_places',
        'create_by',
        'update_by',
    ];

    protected function casts(): array
    {
        return [
            'code' => 'string',
            'short_code' => 'string',
            'name' => 'string',
            'minor_unit' => 'integer',
            'decimal_places' => 'integer',
            'create_by' => 'integer',
            'update_by' => 'integer',
        ];
    }
}
