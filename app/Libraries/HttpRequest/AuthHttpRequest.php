<?php

namespace App\Libraries\HttpRequest;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class AuthHttpRequest
{
    protected static $baseUrl = 'http://127.0.0.1:8009';
    protected static $timeout = 60;
    protected static $retryCount = 3;

    /**
     * Send an authenticated HTTP request with the token from the session.
     *
     * @param callable $callback
     * @return mixed
     */
    public static function sendWithToken(callable $callback)
    {
        $token = Session::get('auth_token');

        $http = Http::acceptJson()
            ->withToken($token)
            ->timeout(self::$timeout)
            ->retry(self::$retryCount, 100)
            ->baseUrl(self::$baseUrl);

        return $callback($http);
    }
}
