<?php
namespace G\Fw\App;

use G\Fw\App;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use G\Fw\Auth;
use G\Fw\Auth\CredentialsValidatorIface;

class Builder
{
    public static  function run(array $values, $path, CredentialsValidatorIface $validator)
    {
        $request = Request::createFromGlobals();

        $silex = new Application($values);

        $authController = new Auth\Controller($validator, $silex);

        $app = new App($silex, $validator, $authController);
        $app->setConf($path);
        $app->run($request);
    }
}
