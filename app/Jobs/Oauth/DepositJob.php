<?php

namespace App\Jobs\Oauth;

use App\Enum\OnQueueEnum;
use App\Jobs\Webhook\FailTransactionJob;
use Throwable;
use App\Models\Wallet\Wallet;
use Illuminate\Bus\Batchable;
use App\Services\Wallet\DepositService;
use Illuminate\Foundation\Queue\Queueable;
use App\Jobs\Webhook\SuccessTransactionJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class DepositJob implements ShouldQueue
{
    use Batchable;
    use Queueable;

    public $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected int|string $userId,
        protected int|float $amount,
        protected ?string $remark = null,
        protected int|string|null $identity = null,
        protected ?string $walletType = null,
        protected ?string $webhookSuccess = null,
        protected ?string $webhookFail = null
    )
    {
        //
    }
    public function backoff(): array
    {
        return [5, 15, 30];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $wallet = Wallet::where('user_id', $this->userId)
                ->where('type', $this->walletType)->firstOrFail();
            //code...\
            $trans = DepositService::dbTransaction()
                ->wallet($wallet)
                ->amount($this->amount)
                ->otherAttribute(['remark' => $this->remark])
                ->confirmed()
                ->create();

            $trxId = $trans->uuid;
            $this->webhookSuccess
                && SuccessTransactionJob::dispatch($this->webhookSuccess, $trxId, $this->identity);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function failed(?Throwable $e): void
    {
        logger()->error(self::class . ': ' .$e);

        if ($this->webhookFail) {
            $message = 'Deposit fail: user_id('.$this->userId.'), amount('.$this->amount.')';
            $message = $this->identity
                ? 'Deposit fail: identity('.$this->identity.')'
                : $message;
        }
        $this->webhookFail
            && FailTransactionJob::dispatch($this->webhookFail, $message, $this->identity)
                ->onQueue(OnQueueEnum::WEBHOOK_DEFAULT->value);
    }
}
