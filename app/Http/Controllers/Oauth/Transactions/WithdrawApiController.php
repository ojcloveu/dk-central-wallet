<?php

namespace App\Http\Controllers\Oauth\Transactions;

use App\Models\User;
use App\Enum\OnQueueEnum;
use Illuminate\Bus\Batch;
use Illuminate\Http\Request;
use App\Libraries\ResponseLib;
use App\Jobs\Oauth\WithdrawJob;
use App\Enum\Wallet\WalletTypeEnum;
use Illuminate\Support\Facades\Bus;
use App\Http\Controllers\Controller;

class WithdrawApiController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $req)
    {
        $req->validate([
            'data' => 'required|array|max:100',
            'data.*' => 'required|array',
            'wallet_type' => 'sometimes|string|in:'.WalletTypeEnum::collect()->values()->implode(','),
            'remark' => 'sometimes|string|max:255',
            'webhook_success' => 'sometimes|url',
            'webhook_fail' => 'sometimes|url'
        ]);

        $json = $req->data;

        $batch = [];
       
        foreach ($json as $data) {
            $batch[] = (new WithdrawJob(
                $data['user_id'],
                $data['amount'],
                $data['remark'] ?? $req->remark ?: null,
                $data['identity'] ?? null,
                $req->wallet_type,
                $req->webhook_success,
                $req->webhook_fail
            ))
                ->onQueue(OnQueueEnum::TRANSACTION_DEFAULT->value);
        }
        
        $batch
            && Bus::batch($batch)
                ->finally(function (Batch $batch) {
          
                })
                ->name('Batch-Withdraw')
                ->allowFailures()
                ->dispatch();

        $batch = [];

        return ResponseLib::success(
            message: 'Successful dispatch.'
        );
    }
}
