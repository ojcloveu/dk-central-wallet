<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Wallet\Wallet;
use App\Libraries\ResponseLib;
use App\Http\Controllers\Controller;
use App\Services\Wallet\TransferService;
use Illuminate\Validation\ValidationException;

class TransferApiController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $req)
    {
        $user = $req->user();
        $from = Wallet::FindAccountNumber($req->from)->first();

        $to = Wallet::FindAccountNumber($req->to)->first();

        $req->validate([
            'from' => [
                'required',
                'string',
                'min:9',
                fn ($a, $v, $fail) => ! $from && $fail('validation.exists')->translate() 
            ],
            'to' => [
                'required',
                'string',
                'min:9',
                fn ($a, $v, $fail) => ! $to && $fail('validation.exists')->translate() 
            ],
            'amount' => [
                'required',
                'numeric',
                'min:0'
            ]
        ]);
        
        try {
            $withdraw = TransferService::dbTransaction()
                ->from($from)
                ->to($to)
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
            message: 'Transfer '.$req->amount.$from->meta['short_code'].' to '.$to->AccountNumber.' successfully.',
            data: $withdraw
        );
    }
}
