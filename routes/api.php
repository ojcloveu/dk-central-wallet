<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DepositApiController;
use App\Http\Controllers\Api\TransferApiController;
use App\Http\Controllers\Api\WithdrawApiController;
Route::middleware('auth:api')->group(function () {
    Route::post('deposit', DepositApiController::class);
    Route::post('withdraw', WithdrawApiController::class);
    Route::post('transfer', TransferApiController::class);
});

Route::post('register', [AuthController::class,'register']);
Route::post('login', [AuthController::class,'login']);
Route::post('refresh', [AuthController::class,'refresh']);
