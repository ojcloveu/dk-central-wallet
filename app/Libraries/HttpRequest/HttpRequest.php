<?php

namespace App\Libraries\HttpRequest;

use Closure;
use App\Enum\SessionEnum;
use Illuminate\Support\Str;
use App\Enum\SessionErrorEnum;
use App\Libraries\HttpRequest\ErrorStatusTrait;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Client\PendingRequest;

class HttpRequest
{
    public $http;
    public $timeout = 60;
    public bool|string|null $token = false;
    public string $tokenType = 'Bearer';
    public array $headers = [];

    public function __construct(public ?string $url = null)
    {
        ! $url && $this->url = config('backpack.base.dk666_url');
    }

    /**
     * INITIALIZE METHODS
     */
    public static function make(?string $url = null)
    {
        return new static($url);
    }

    /**
     * FINAL METHODS
     */
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
        ! $fnIssetHeader('Content-Type') && $this->headers['Content-Type'] = 'application/json';

        if ($this->headers) {
            $http->withHeaders($this->headers);
        }

        $http = $callback($http, $this->url);

        $this->http = $http;

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
            // $token = SessionEnum::token();
        }

        $this->token = $token;
        $this->tokenType = $type;

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

    public function withCustomTokenHeader()
    {
        $this->withHeader('Access-Token', env('HTTP_CUSTOM_TOKEN'));

        return $this;
    }
}
