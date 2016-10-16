<?php
namespace G\Fw\Auth;

interface CredentialsValidatorIface
{
    public function validate($user, $pass);

    public function getDecodedToken($token, $key);

    public function getNewPreToken($appName, $user);

    public function getNewToken($appName, $user);

    public function getMessage();

    public function setUp(array $conf);
}