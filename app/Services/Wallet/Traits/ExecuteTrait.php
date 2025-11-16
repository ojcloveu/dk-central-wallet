<?php

namespace App\Services\Wallet\Traits;

use Illuminate\Support\Facades\DB;

trait ExecuteTrait
{
    public function create()
    {
        return $this->dbTransaction
            ? $this->createWithDb()
            : $this->createWithoutDb();
    }

    public function update()
    {
        return $this->dbTransaction
            ? $this->updateWithDb()
            : $this->updateWithoutDb();
    }
}