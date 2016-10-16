<?php
namespace G\Fw\Auth;

use Firebase\JWT\JWT;

trait CredentialsJWTTrait
{
    protected $message;
    protected $secret         = "superSecretKey";
    protected $preTokeExpires = "+20 days";
    protected $tokeExpires    = '+1 hour';
    protected $allowedTries   = 3;

    public function setUp(array $conf)
    {
        $this->secret         = isset($conf['secret']) ? $conf['secret'] : $this->secret;
        $this->preTokeExpires = isset($conf['preTokeExpires']) ? $conf['preTokeExpires'] : $this->preTokeExpires;
        $this->tokeExpires    = isset($conf['tokeExpires']) ? $conf['tokeExpires'] : $this->tokeExpires;
        $this->allowedTries   = isset($conf['allowedTries']) ? $conf['allowedTries'] : $this->allowedTries;
    }

    public function getDecodedToken($token, $key)
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
            "exp"      => (new \DateTimeImmutable())->modify($this->preTokeExpires)->getTimestamp(),
            "user"     => $user,
            "appName"  => $appName,
            "pretoken" => true
        ], $token);
    }

    public function getNewToken($appName, $user)
    {
        return JWT::encode([
            "exp"      => (new \DateTimeImmutable())->modify($this->tokeExpires)->getTimestamp(),
            "user"     => $user,
            "appName"  => $appName,
            "pretoken" => false,
        ], $this->secret);
    }

    public function getMessage()
    {
        return $this->message;
    }
}