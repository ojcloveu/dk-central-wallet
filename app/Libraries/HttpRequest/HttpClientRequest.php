<?php

namespace App\Libraries\HttpRequest;

use Closure;
use App\Enum\SessionEnum;
use Illuminate\Support\Str;
use App\Enum\SessionErrorEnum;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Request;

class HttpClientRequest
{
    use ErrorStatusTrait;

    public $http;
    public $timeout = 60;
    public bool|string|null $token = false;
    public string $tokenType = 'Bearer';
    public array $headers = [];
    public $form;

    public function __construct(public ?string $url = null)
    {
        ! $url && $this->url = 'http://127.0.0.1:8001';
    }

    /**
     * INITIALIZE METHODS
     */
    public static function make(?string $url = null)
    {
        return new static($url);
    }

    public static function secretId()
    {
        return env('HTTP_SECRET_ID');
    }

    public static function secretToken()
    {
        return env('HTTP_SECRET_TOKEN');
    }

    public static function oauthData($id = null, $token = null, $scope = null)
    {
        return [
            'grant_type' => 'client_credentials',
            'client_id' => $id ?: self::secretId(),
            'client_secret' => $token ?: self::secretToken(),
            'scope' => $scope ?: '',
        ];
    }

    public static function oauthToken()
    {
        return self::make()->create(
            fn ($http, $url) => $http->post($url.'/oauth/token', self::oauthData())
        )->json()['access_token'] ?? null;
    }

    /**
     * FINAL METHODS
     */
    public function createWithSession(callable|Closure $callback, $excepts = [])
    {
        $http = $callback(
            $this->create(fn ($http, $url) => $http),
            $this->url
        );

        $fnIsInExcepts = fn ($v) => in_array($v, $excepts);

        if ($http->failed()) {
            match($http->status()) {
                401, "401" => $this->errorStatus401($fnIsInExcepts(401)),
                403, "403" => $this->errorStatus403($fnIsInExcepts(403)),
                422, "422" => $this->errorStatus422($http, $fnIsInExcepts(422)),
                400, "400" =>  $this->errorStatus400($http, $fnIsInExcepts(400)),
                default => Session::flash(
                    SessionErrorEnum::HTTP_ERROR_MESSAGE->value,
                    'An unexpected error occurred: '.$http->status()
                ),
            };
        }

        return $http;
    }

    public function create(callable|Closure $callback)
    {
        $http = Http::timeout($this->timeout);

        if ($this->token !== false) {
            $http->withToken($this->token, $this->tokenType);
        }

        $fnIssetHeader = fn ($key) => collect($this->headers)
            ->filter(fn ($v, $k) => Str::lower($k) == Str::lower($key))
            ->count();

        // enforce application/json when not define
        ! $fnIssetHeader('Accept') && $this->headers['Accept'] = 'application/json';
        if (! $this->form) {
            ! $fnIssetHeader('Content-Type') && $this->headers['Content-Type'] = 'application/json';
        }

        if ($this->headers) {
            $http->withHeaders($this->headers);
        }

        $http = $callback($http, $this->url);

        $this->http = $http;

        if ($this->form) {
            $this->http = $this->http->{$this->form}();
        }

        return $this->http;
    }

    /**
     * CHAIN METHODS
     */

    public function timeout(int $timeout): static
    {
        $this->timeout = $timeout;

        return $this;
    }

    public function withToken(?string $token = null, string $type = 'Bearer'): static
    {
        if (! $token) {
            // $token = session(SessionEnum::TOKEN->value);
            $token = SessionEnum::token();
        }

        $this->token = $token;
        $this->tokenType = $type;

        return $this;
    }

    public function withOauthToken()
    {
        $this->withToken(self::oauthToken());

        return $this;
    }

    public function withHeader(string $key, $value): static
    {
        $this->headers[$key] = $value;

        return $this;
    }

    public function withHeaders(array $headers): static
    {
        $this->headers = $headers;

        return $this;
    }

    public function useRedirectDomain(): static
    {
        $this->url = config('app.dk_url');

        return $this;
    }

    public function withCustomTokenHeader()
    {
        $this->withHeader('Access-Token', env('HTTP_CUSTOM_TOKEN'));

        return $this;
    }

    public function asForm()
    {
        $this->form = 'asForm';
        return $this;
    }

    public function asMultipart()
    {
        $this->form = 'asMultipart';
        return $this;
    }
}
