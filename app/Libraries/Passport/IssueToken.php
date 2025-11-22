<?php

namespace App\Libraries\Passport;

use Laravel\Passport\Client;
use App\Libraries\Passport\Traits\ClientTrait;
use App\Libraries\Passport\Traits\ExecuteTrait;
use App\Libraries\Passport\Traits\GrantTypeTrait;
use App\Libraries\Passport\Traits\PasswordSkipTrait;
class IssueToken
{
    use ClientTrait;
    use PasswordSkipTrait;
    use GrantTypeTrait;
    use ExecuteTrait;

    protected string|bool $issueTokenField = false;
    
    protected $usernameValue;

    public function __construct(
        public string|array $scope,
        public string $username
    ) {
        $this->scope = (array) $scope;

        $this->usePasswordGrantType();

        $this->useUserName($username);
    }

    /**
     * Static Methods
     */

    public static function scope(
        string|array $scope = [],
        string $userField = 'email'
    ): static {
        return new static($scope, $userField);
    }

    /*
     * Non Static Methods
     */

    public function setUsernameValue($value): static
    {
        $this->usernameValue = $value;

        return $this;
    }

    public function useUserName($key): static
    {
        $this->username = $key;

        $this->issueTokenField = static::encrypt($key);

        return $this;
    }

    protected function defaultParam(): array
    {   
        return [
            'grant_type' => $this->grantType,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'scope' => collect($this->scope)->implode(' '),
            static::ISSUE_TOKEN_FIELD => $this->issueTokenField
        ];
    }
}
