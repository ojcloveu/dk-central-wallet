<?php

namespace App\Services\Wallet\Traits;

use Illuminate\Support\Facades\DB;

trait DbTrait
{
    protected $retry;
    
    protected function createWithDb()
    {
        return DB::transaction(fn () => $this->createWithoutDb(), $this->retry);
    }

    protected function updateWithDb()
    {
        return DB::transaction(fn () => $this->updateWithoutDb(), $this->retry);
    }
}