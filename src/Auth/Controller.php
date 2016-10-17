<?php
namespace G\Bck\Auth;

use G\Bck\BuilderIface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class Controller
{
    private $credentialsValidator;
    private $app;
    const VALIDATE_CREDENTIALS = 'validateCredentials';
    const VALIDATE_TOKEN_2FA = 'validateToken2FA';

    public function __construct(CredentialsValidatorIface $credentialsValidator, Application $app)
    {
        $this->credentialsValidator = $credentialsValidator;
        $this->app                  = $app;
    }

    public function isValidRoute(Request $request)
    {
        $pathInfo     = $request->getPathInfo();
        $pathInfoData = explode("/", $pathInfo);

        return !in_array($pathInfoData[count($pathInfoData) - 1], [self::VALIDATE_TOKEN_2FA, self::VALIDATE_CREDENTIALS]);
    }

    public function validateToken2FA(Application $app, Request $request)
    {
        $preToken = $this->credentialsValidator->getDecodedToken($request->get('jwt'), $request->get('sms'));
        $user     = $request->get('user');

        $app['user'] = $user;
        if ($preToken !== false && $preToken->user == $user) {
            $jwt = $this->credentialsValidator->getNewToken($this->app['appName'], $user);
            /** @var BuilderIface $builder */
            $builder = new $app['builder']($request, $app);
            $builder->preFetch();

            return $app->json(['status' => true, 'jwt' => $jwt]);
        } else {
            return $app->json(['status' => false, 'message' => 'NOT_VALID_TOKEN']);
        }
    }

    public function validateCredentials(Application $app, Request $request)
    {
        $user     = $request->get('user');
        $password = $request->get('password');

        $app['user'] = $user;
        if ($this->credentialsValidator->validate($user, $password)) {
            if ($app['twoFactorAuthentication']) {
                $jwt = $this->credentialsValidator->getNewPreToken($this->app['appName'], $request->get('user'));
            } else {
                /** @var BuilderIface $builder */
                $builder = new $app['builder']($request, $app);
                $builder->preFetch();
                $jwt = $this->credentialsValidator->getNewToken($this->app['appName'], $request->get('user'));
            }

            return $app->json(['status' => true, 'jwt' => $jwt]);
        } else {
            return $app->json(['status' => false, 'message' => $this->credentialsValidator->getMessage()]);
        }
    }

    public function getControllerFactory()
    {
        $auth = $this->app['controllers_factory'];
        $auth->post('/' . self::VALIDATE_CREDENTIALS, [$this, self::VALIDATE_CREDENTIALS]);
        $auth->post('/' . self::VALIDATE_TOKEN_2FA, [$this, self::VALIDATE_TOKEN_2FA]);

        return $auth;
    }
}