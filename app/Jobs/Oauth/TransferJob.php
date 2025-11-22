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
use App\Services\Wallet\TransferService;
use App\Services\Wallet\WithdrawService;
use Illuminate\Contracts\Queue\ShouldQueue;

class TransferJob implements ShouldQueue
{
    use Batchable;
    use Queueable;

    public $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected int|string $from,
        protected int|string $to,
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
            $from = Wallet::where('user_id', $this->from)
                ->where('type', $this->walletType)->firstOrFail();

            $to = Wallet::where('user_id', $this->to)
                ->where('type', $this->walletType)->firstOrFail();
            //code...\
            $trans = TransferService::dbTransaction()
                ->from($from)
                ->to($to)
                ->amount($this->amount)
                ->otherAttribute(['remark' => $this->remark])
                ->confirmed()
                ->create();

            $this->webhookSuccess
                && SuccessTransactionJob::dispatch($this->webhookSuccess, $trans, $this->identity);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function failed(?Throwable $e): void
    {
        logger()->error(self::class . ': ' .$e);

        if ($this->webhookFail) {
            $message = 'Withdraw fail: user_id('.$this->userId.'), amount('.$this->amount.')';
            $message = $this->identity
                ? 'Withdraw fail: identity('.$this->identity.')'
                : $message;
        }
        $this->webhookFail
            && FailTransactionJob::dispatch($this->webhookFail, $message, $this->identity)
                ->onQueue(OnQueueEnum::WEBHOOK_DEFAULT->value);
    }
}
