<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Wallet\Wallet;
use App\Libraries\ResponseLib;
use App\Http\Controllers\Controller;
use App\Services\Wallet\WalletService;
use App\Services\Wallet\WithdrawService;
use Illuminate\Validation\ValidationException;

class WithdrawApiController extends Controller
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
            $withdraw = WithdrawService::dbTransaction()
                ->wallet($wallet)
                ->amount($req->amount)
                ->confirmed()
                ->create();
        } catch (ValidationException $th) {
            return ResponseLib::validateError($th);
        } catch (\Throwable $th) {
            logger()->error(self::class . ' Error: '.$th);

            return ResponseLib::error(
                message: 'Withdraw fail: ' . $th->getMessage()
            );
        }

        return ResponseLib::success(
            message: 'Withdraw '.$req->amount.$wallet->meta['short_code'].' from '.$wallet->AccountNumber.' successfully.',
            data: $withdraw
        );
    }
}
