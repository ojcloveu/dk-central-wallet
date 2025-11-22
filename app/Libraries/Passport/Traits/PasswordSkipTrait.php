<?php

namespace App\Libraries\Passport\Traits;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Request;

trait PasswordSkipTrait
{
    public const WITHOUT_PASSWORD = 'validateForPassportPasswordGrant';
    public const ISSUE_TOKEN_FIELD = 'issueTokenField';
    protected string|bool $withoutPassword = false;

    protected function passwordParam($password): array
    {
        return is_string($this->withoutPassword) && $this->withoutPassword
        ? [
            'password' => $this->withoutPassword,
            static::WITHOUT_PASSWORD => $this->withoutPassword
        ]
        : ['password' => $password];
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
    
    public static function isPasswordSkipped(): bool
    {
        $inputField = static::WITHOUT_PASSWORD;
        $input = request()->request->get($inputField) ?: request()->{$inputField};
        return static::decrypt($input) ? true : false;
    }

    public function skipPasswordCheck(): static
    {
        // set random string to encrypt, just check decrypt success or not after
        $this->withoutPassword = static::encrypt(Str::random(10));

        return $this;
    }

    public static function issueTokenField()
    {
        $inputField = static::ISSUE_TOKEN_FIELD;
        return static::decrypt(request()->request->get($inputField) ?: request()->{$inputField});
    }
}