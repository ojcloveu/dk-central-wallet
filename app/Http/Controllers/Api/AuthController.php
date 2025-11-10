<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Libraries\ResponseLib;
use App\Enum\Passports\ScopeEnum;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Libraries\Setting\SettingLib;
use App\Libraries\UserApi\UserApiLib;
use App\Libraries\Passport\IssueToken;
use App\Libraries\Telegram\TelegramLib;
use App\Http\Resources\AuthGameResource;
use App\Enum\MediaLibrary\UserCollection;
use App\Libraries\HttpRequest\HttpRequest;
use App\Http\Resources\Api\User\AuthResource;

class AuthController extends Controller
{
    protected $scope = ScopeEnum::USER->value;

    protected $userModel;
    protected $userNameField = 'telegram_id';

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function authorizeUser(Request $req)
    {
        return new AuthResource($req->user());
    }

    protected function findUser($value): ?User
    {
        return IssueToken::findUser('email', $value);
    }

    public function refresh(Request $req)
    {
        $req->validate(['refresh_token' => 'required']);

        $res = IssueToken::scope($this->scope, 'email')
            ->useRefreshTokenGrantType()
            ->issueToken($req);

        if (! $res->success) {
            return ResponseLib::error(
                data: ['hint' => $res->json['hint'] ?? ''],
                message: $res->json['message'] ?? 'The refresh token is invalid.',
                status: 400,
            );
        }

        return ResponseLib::success(
            message: 'Token has been refresh successful.',
            additional: ['token' => $res->json]
        );
    }

    public function login(Request $req)
    {
        $req->headers->set('Accept', 'application/json');

        $req->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        try {
            $user = User::where('email', $req->email)->first();

            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }

            if (! Hash::check($req->password, $user->password)) {
                return response()->json(['error' => 'Invalid credentials'], 401);
            }

            $res = IssueToken::scope($this->scope, 'email')
                ->usePasswordGrantType()
                ->issueToken($req);

            return (new AuthResource($user))->additional([
                'token' => $res->json,
            ]);

        } catch (\Throwable $e) {
            logger()->error('Error in method: ' . __METHOD__, [
                'exception' => $e,
                'custom_data' => optional($req)->all(),
            ]);

            report($e);

            return response()->json([
                'error' => 'Authentication failed',
                'message' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    protected function verifiedLogin(User $user, Request $req, $token = null)
    {
        $res = IssueToken::scope($this->scope, 'email')
            ->usePasswordGrantType()
            ->skipPasswordCheck()
            ->issueToken($req);

        return (new AuthResource($user))->additional([
            'token' => $res->json,
            // 'dk_token' => $token
        ]);
    }

    public function register(Request $req)
    {
        $req->headers->set('Accept', 'application/json'); 
        $req->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);
       
        try {
            DB::beginTransaction();
            
            $user = new User();
            $user->email= $req->email;
            $user->name = $req->email;
            $user->password = $req->password;
            $user->save();

            DB::commit();

            return $this->verifiedLogin($user, $req);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error(self::class. '::register() : ' . $th);
            return ResponseLib::error(message: $th->getMessage());
        }
    }

    public function logout(Request $req)
    {
        try {
            $token = $req->user()->token();

            DB::table('oauth_refresh_tokens')
                ->where('access_token_id', $token->id)
                ->update(['revoked' => true]);

            $token->revoke();

            return ResponseLib::success(message: 'Logout was successful.');
        } catch (\Throwable $e) {
            return ResponseLib::error(message: 'Failed to logout.');
        }
    }

    
}
