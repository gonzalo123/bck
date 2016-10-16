<?php
namespace G\Fw\Auth;

use Firebase\JWT\JWT;

trait CredentialsJWTTrait
{
    protected $message;

    public function getDecodedToken($token, $key = self::SECRET)
    {
        try {
            $decoded = JWT::decode($token, $key, ['HS256']);

            return $decoded;
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function emit2FA($token)
    {
    }

    public function getNewPreToken($appName, $user)
    {
        $token = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $this->emit2FA($token);

        return JWT::encode([
            "exp"      => (new \DateTimeImmutable())->modify(self::PRE_TOKEN_EXPIRES)->getTimestamp(),
            "user"     => $user,
            "appName"  => $appName,
            "pretoken" => true
        ], $token);
    }

    public function getNewToken($appName, $user)
    {
        return JWT::encode([
            "exp"      => (new \DateTimeImmutable())->modify(self::TOKEN_EXPIRES)->getTimestamp(),
            "user"     => $user,
            "appName"  => $appName,
            "pretoken" => false,
        ], self::SECRET);
    }

    public function getMessage()
    {
        return $this->message;
    }
}