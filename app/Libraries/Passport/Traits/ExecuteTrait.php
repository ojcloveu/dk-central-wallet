<?php

namespace App\Libraries\Passport\Traits;

use App\Libraries\Passport\GrantTypeEnum;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Request;

trait ExecuteTrait
{
    /**
     * Issue Oauth Token
     *
     * @return object<res|statusCode|json|success>
     */
    public function create($req): object
    {
        $this->loadCLientIfMissing();

        $defaultParam = $this->defaultParam();
        
        // when not set username value then get from $req
        if (! $this->usernameValue) {
            $this->setUsernameValue($req->{$this->username});
        }

        $params = match($this->grantType) {
            GrantTypeEnum::PASSWORD->value => [
                ...$defaultParam,
                ...[
                    'username' => $this->usernameValue,
                    ...$this->passwordParam($req->password)
                ]
            ],
            GrantTypeEnum::REFRESH_TOKEN->value => [
                ...$defaultParam,
                ...['refresh_token' => $req->refresh_token]
            ],
            default => []
        };
        
        $req->request->add([...$params]);

        $req = Request::create('/oauth/token', 'POST', $params);

        $req->headers->set('Accept', 'application/json');
        $req->headers->set('Content-Type', 'application/json');

        $res = Route::dispatch($req);
        $statusCode = $res->getStatusCode();

        return (object)[
            'res' => $res,
            'statusCode' => $statusCode,
            'json' => json_decode($res->getContent(), true),
            'success' => $statusCode == '200',
        ];
    }

    public static function findUser($key, $value)
    {
        return User::where($key, $value)->first();
    }
}