<?php

namespace App\Libraries\Passport;

use App\Models\User;
use Illuminate\Support\Str;
use Laravel\Passport\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Request;

/**
 * By default
 *
 * Client set to record number 2 - setClient()
 *
 * Username request field set to phone -> usePhone(), userEmail()
 */
class IssueToken
{
    /**
     * Request inout key for validate encrypted string
     */
    public const WITHOUT_PASSWORD = 'validateForPassportPasswordGrant';

    public const ISSUE_TOKEN_FIELD = 'issueTokenField';

    public static function staticPassportId()
    {
        return env('PASSPORT_PASSWORD_ID');
    }

    public static function nonStaticPassportId()
    {
        return env('PASSPORT_PASSWORD_ID');
    }

    public static function staticPassportSecret()
    {
        return env('PASSPORT_PASSWORD_SECRET');
    }

    public static function nonStaticPassportSecret()
    {
        return env('PASSPORT_PASSWORD_SECRET');
    }
    /**
     * Encrypt string
     */
    protected string|bool $withoutPassword = false;

    /**
     * Encrypt string
     */
    protected string|bool $issueTokenField = false;

    /**
     * Client Model
     */
    protected Client|bool $client = false;

    /**
     * Oauth Type
     */
    protected string $grantType;

    /**
     * Request input key for username
     */
    // protected string $username = 'username';

    /**
     * Username input value
     */
    protected $usernameValue;

    public function __construct(
        public string|array $scope,
        public string $username,
        public string|int $clientId
    ) {
        $this->scope = (array) $scope;

        // Default configuration below

        $this->usePasswordGrantType();

        $this->setClient($clientId);

        $this->useUserName($username);
    }

    /**
     * Static Methods
     */

    public static function scope(
        string|array $scope = [],
        string $userField = 'telegram_id',
        string|int|null $clientId = null
    ): static {
        $clientId = $clientId ?: self::nonStaticPassportId();
        return new static($scope, $userField, $clientId);
    }

    public static function scopeUsername(
        string|array $scope = [],
        string $userField = 'username',
        string|int|null $clientId = null
    ): static {
        $clientId = $clientId ?: self::nonStaticPassportId();
        return new static($scope, $userField, $clientId);
    }

    public static function encrypt(...$value): string
    {
        return encrypt(...$value);
    }

    public static function decrypt(...$value)
    {
        try {
            return decrypt(...$value);
        } catch (\Throwable $th) {
            Log::error(self::class.': '.$th->getMessage());
        }

        return false;
    }

    public static function issueTokenField()
    {
        $inputField = static::ISSUE_TOKEN_FIELD;
        return static::decrypt(request()->request->get($inputField) ?: request()->{$inputField});
    }

    public static function isPasswordSkipped(): bool
    {
        $inputField = static::WITHOUT_PASSWORD;
        $input = request()->request->get($inputField) ?: request()->{$inputField};
        return static::decrypt($input) ? true : false;
    }

    /*
     * Non Static Methods
     */

    public function setClient(Client|int|string $client): static
    {
        // $this->client = is_numeric($client) || is_string($client)? Client::find($client) : $client;
        // dd($this->client);

        // if (! $this->client) {
        //     throw new \UnexpectedValueException('Client not found.');
        // }

        return $this;
    }

    /**
     * Set Grant Type
     *
     * @param string<password|refresh_token> $value
     */
    protected function setGrantType(string $value): static
    {
        $this->grantType = $value;

        return $this;
    }

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

    public function usePasswordGrantType(): static
    {
        $this->setGrantType('password');

        return $this;
    }

    public function useRefreshTokenGrantType(): static
    {
        $this->setGrantType('refresh_token');

        return $this;
    }

    public function skipPasswordCheck(): static
    {
        // set random string to encrypt, just check decrypt success or not after
        $this->withoutPassword = static::encrypt(Str::random(10));

        return $this;
    }

    protected function defaultParam(): array
    {
        return [
            'grant_type' => $this->grantType,
            // 'client_id' => $this->client->id,
            // 'client_secret' => $this->client->secret,
            'client_id' => $this->nonStaticPassportId(),
            'client_secret' => $this->nonStaticPassportSecret(),
            'scope' => collect($this->scope)->implode(' '),
            static::ISSUE_TOKEN_FIELD => $this->issueTokenField
        ];
    }

    protected function passwordParam($password): array
    {
        return is_string($this->withoutPassword) && $this->withoutPassword
        ? [
            'password' => $this->withoutPassword,
            static::WITHOUT_PASSWORD => $this->withoutPassword
        ]
        : ['password' => $password];
    }

    /**
     * Issue Oauth Token
     *
     * @return object<res|statusCode|json|success>
     */
    public function issueToken($req): object
    {
        $defaultParam = $this->defaultParam();

        // when not set username value then get from $req
        if (! $this->usernameValue) {
            $this->setUsernameValue($req->{$this->username});
        }

        $params = match($this->grantType) {
            'password' => [...$defaultParam, ...[
                'username' => $this->usernameValue,
                ...$this->passwordParam($req->password)
            ]],
            'refresh_token' => [...$defaultParam, ...['refresh_token' => $req->refresh_token]],
            default => []
        };
        
        $req->request->add([...$params]);

        $req = Request::create('/oauth/token', 'POST', $params);

        $req->headers->set('Accept', 'application/json');
        $req->headers->set('Content-Type', 'application/json');

        $res = Route::dispatch($req);

        return (object)[
            'res' => $res,
            'statusCode' => $statusCode = $res->getStatusCode(),
            'json' => json_decode($res->getContent(), true),
            'success' => $statusCode == '200',
        ];
    }

    public static function findUser($key, $value)
    {
        return User::where($key, $value)->first();
    }
}
