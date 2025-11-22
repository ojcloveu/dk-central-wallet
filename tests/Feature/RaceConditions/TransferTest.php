<?php

use Spatie\Fork\Fork; 
use App\Models\Wallet\Wallet;
use App\Services\Wallet\DepositService;
use Illuminate\Support\Facades\DB;
use App\Services\Wallet\TransferService;
use App\Services\Wallet\WalletService;
use App\Services\Wallet\WithdrawService;
use Database\Seeders\Production\BaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

// php artisan test --filter=TransferTest

uses(DatabaseMigrations::class); // Add this line

// uses(RefreshDatabase::class); // Add this line

test('transfer race conditions', function () {
    $this->seed(BaseSeeder::class); 
    // Create wallets
    $initBalance = 1000;
    Wallet::where('id', 1)->update(['balance' => $initBalance]);
    Wallet::where('id', 2)->update(['balance' => $initBalance]);
    $from = Wallet::find(1);
    $to = Wallet::find(2);

    $amount = 1; // Transfer amount per request
    $numberOfRequests = 100; // Number of simulated concurrent transfers
    $totalAmount = $amount * $numberOfRequests;

    expect($from->balance)->toBe($initBalance); // deposit
    expect($to->balance)->toBe($initBalance); // deposit

    $requests = [];
    for ($i = 0; $i < $numberOfRequests; $i++) {
        $requests[] = function () use ($from, $to, $amount) {
             TransferService::dbTransaction()
                    ->from($from)
                    ->to($to)
                    ->amount($amount)
                    ->confirmed()
                    /**
                     * forced()
                     * if i don't enable this to disable validate rule, it will error when 100 at same time
                     * as it prevent amount from below 0 so it fail match the expect
                     */
                    ->forced()
                    ->create();
        };
    }
    // dd(count($requests));
    Fork::new()
        ->concurrent($numberOfRequests) // Set concurrency explicitly
        // Force the connection to close and reopen for each child process
        ->before(function () {
            DB::purge(); // Close all connections
            DB::connection()->reconnect(); // Re-establish the default connection
        }) 
        ->run(...$requests); 

    // DB::purge();
    // DB::connection()->reconnect();
    $from = $from->fresh();
    $to = $to->fresh();

    // Assertions
    expect($from->balance)->toBe($initBalance - ($totalAmount * $from->metaMinorUnit)); // deposit
    expect($to->balance)->toBe($initBalance + ($totalAmount * $to->metaMinorUnit)); // deposit
});
