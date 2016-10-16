<?php
namespace Demo;

use G\Fw\Auth\CredentialsJWTTrait;
use G\Fw\Auth\CredentialsValidatorIface;

class CredentialsValidator implements CredentialsValidatorIface
{
    use CredentialsJWTTrait;

    const SECRET = 'ad89006cdad4efa2f1a708a52c2b794a';
    const PRE_TOKEN_EXPIRES = '+1 hour';
    const TOKEN_EXPIRES = '+20 days';
    const ALLOWED_TRIES = 3;

    public function validate($user, $pass)
    {
        if ($user == 'gonzalo' && $pass == 'gonzalo') {
            return true;
        } else {
            $this->message = 'ACCESS_DENIED';

            return false;
        }
    }

    protected function emit2FA($token)
    {
        error_log($token);
    }
}