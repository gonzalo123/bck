<?php
namespace G\Fw\App;

use G\Fw\App;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use G\Fw\Auth;
use G\Fw\Auth\CredentialsValidatorIface;
use Psr\Log\LoggerInterface;

class Builder
{
    public static  function run(array $values, $path, CredentialsValidatorIface $validator, LoggerInterface $logger)
    {
        $request = Request::createFromGlobals();

        $silex = new Application($values);

        $authController = new Auth\Controller($validator, $silex);

        $app = new App($silex, $validator, $authController, $logger);
        $app->setConf($path);
        $app->run($request);
    }
}
