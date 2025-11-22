<?php

namespace App\Jobs\Webhook;

use App\Libraries\HttpRequest\HttpRequest;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SuccessTransactionJob implements ShouldQueue, ShouldBeUnique
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected string $url,
        protected string $msg,
        protected int|string|null $identity
    )
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        HttpRequest::make($this->url)->create(
            fn ($http, $url) => $http->post($url, [
                'trx' => $this->msg,
                'identity' => $this->identity
            ]) 
        );
    }
}
