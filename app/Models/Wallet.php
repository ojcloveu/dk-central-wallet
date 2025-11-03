<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $fillable = ['uuid','holder_type','holder_id','currency_code','decimal_places','balance','account_number'];
}
