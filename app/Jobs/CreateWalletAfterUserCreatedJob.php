<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\Wallet\WalletService;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;

class CreateWalletAfterUserCreatedJob implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(protected int $userId)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        WalletService::createWallet($this->userId);
    }
}
