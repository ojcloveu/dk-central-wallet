<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Wallet\Wallet;
use App\Libraries\ResponseLib;
use App\Http\Controllers\Controller;
use App\Services\Wallet\DepositService;
use App\Services\Wallet\WalletService;

class DepositApiController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $req)
    {
        $user = $req->user();
        $wallet = Wallet::FindAccountNumber($req->account_number)->first();

        $req->validate([
            'account_number' => [
                'required',
                'string',
                'min:9',
                fn ($a, $v, $fail) => ! $wallet && $fail('validation.exists')->translate() 
            ],
            'amount' => [
                'required',
                'numeric',
                'min:0'
            ]
        ]);

        try {
            $deposit = DepositService::dbTransaction()
                ->wallet($wallet)
                ->amount($req->amount)
                ->confirmed()
                ->create();
        } catch (\Throwable $th) {
            logger()->error(self::class . ' Error: '.$th);

            return ResponseLib::error(
                message: 'Deposit fail: ' . $th->getMessage()
            );
        }

        return ResponseLib::success(
            message: 'Deposit '.$req->amount.$wallet->meta['short_code'].' to '.$wallet->AccountNumber.' successfully.',
            data: $deposit
        );
    }
}
