<?php

namespace App\Libraries\Passport;

use Illuminate\Support\Facades\Hash;
use App\Libraries\Passport\IssueToken;

/**
 * IssueTokenTrait For Passport
 *
 * use \App\Libraries\Passport\IssueTokenTrait;
 */
trait IssueTokenTrait
{
    public function findForPassport(?string $username)
    {
        return $this->where(IssueToken::issueTokenField(), $username)->first();
    }

    public function validateForPassportPasswordGrant($password)
    {
        // Allow to password grant without submit password
        // Reason:
        // Avoid to mixed use case between password grant token & personal token
        // Avoid old personal token is still valid after re-login or revoked

        if ($enabled = IssueToken::isPasswordSkipped()) {
            return $enabled;
        }

        // Else password check is required
        return Hash::check($password, $this->password);
    }
}