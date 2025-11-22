<?php

namespace App\Libraries\Passport\Traits;

use App\Libraries\Passport\GrantTypeEnum;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Request;

trait GrantTypeTrait
{
    protected string $grantType;
    
    protected function setGrantType(string $value): static
    {
        $this->grantType = $value;

        return $this;
    }

    public function usePasswordGrantType(): static
    {
        $this->setGrantType(GrantTypeEnum::PASSWORD->value);

        return $this;
    }

    public function useRefreshTokenGrantType(): static
    {
        $this->setGrantType(GrantTypeEnum::REFRESH_TOKEN->value);

        return $this;
    }
}