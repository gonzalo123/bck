<?php
namespace Demo;

use G\Fw\Auth\CredentialsJWTTrait;
use G\Fw\Auth\CredentialsValidatorIface;

class CredentialsValidator implements CredentialsValidatorIface
{
    use CredentialsJWTTrait;

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