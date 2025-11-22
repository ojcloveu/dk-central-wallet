<?php

use App\Enum\Passports\ScopeEnum;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\RateLimiter;
use App\Http\Controllers\Oauth\Transactions\DepositApiController;
use Laravel\Passport\Http\Middleware\EnsureClientIsResourceOwner;
use App\Http\Controllers\Oauth\Transactions\TransferApiController;
use App\Http\Controllers\Oauth\Transactions\WithdrawApiController;

// Route::get('test/check-rate-limit', fn () => RateLimiter::limiter('heavy_client_api') ? 'registered' : 'not found');
Route::middleware(EnsureClientIsResourceOwner::using(ScopeEnum::OAUTH->value))
    ->prefix('s2s-api')
    ->group(function () {
        Route::post('deposit', DepositApiController::class);
        Route::post('withdraw', WithdrawApiController::class);
        Route::post('transfer', TransferApiController::class);
    });