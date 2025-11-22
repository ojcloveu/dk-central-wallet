<?php

namespace App\Libraries\Passport\Traits;

use App\Libraries\Passport\GrantTypeEnum;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Request;

trait ClientTrait
{
    protected int|string|null $clientId = null;
    protected ?string $clientSecret = null;

    public static function passwordId()
    {
        return env('PASSPORT_PASSWORD_ID') ?: null;
    }

    public static function passwordSecret()
    {
        return env('PASSPORT_PASSWORD_SECRET') ?: null;
    }

    public function setClient(int|string $clientId, string $clientSecret): static
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        
        return $this;
    }

    protected function loadCLientIfMissing()
    {
        if (! $this->clientId || ! $this->clientSecret) {
            $password = [$this->passwordId(), $this->passwordSecret()];

            $client = match ($this->grantType) {
                GrantTypeEnum::PASSWORD->value => $password,
                GrantTypeEnum::REFRESH_TOKEN->value => $password,
                default => [null, null]
            };

            $this->setClient($client[0], $client[1]);
        }
    }
}