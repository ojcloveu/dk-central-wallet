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
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'code' => 'string',
            'short_code' => 'string',
            'name' => 'string',
            'minor_unit' => 'integer',
            'decimal_places' => 'integer',
            'created_by' => 'integer',
            'updated_by' => 'integer',
        ];
    }
}
