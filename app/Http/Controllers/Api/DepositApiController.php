<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Wallet\Wallet;
use App\Libraries\ResponseLib;
use App\Http\Controllers\Controller;
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
            $amount = WalletService::amountToDb($req->amount, $wallet->meta['minor_unit']);
            
            WalletService::forceDepositDb(
                $wallet->id,
                $amount,
                confirmed: true,
                meta: $wallet->meta,
                other: [
                    'holder_type' => User::class,
                    'holder_id' => $user->id
                ]
            );
        } catch (\Throwable $th) {
            logger()->error(self::class . ' Error: '.$th);

            return ResponseLib::error(
                message: 'Deposit fail: ' . $th->getMessage()
            );
        }

        return ResponseLib::success(
            message: 'Deposit '.$req->amount.' '.$wallet->meta['short_code'].' to '.$wallet->AccountNumber.' successfully.'
        );
    }
}
