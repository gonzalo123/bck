<?php
namespace G\Fw;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface BuilderIface
{
    public function __construct(Request $request, Application $app);

    public function getType();

    public function getSubType();

    public function fetch();

    public function finish(Request $request, Response $response);

    public function preFetch();

    public function setRoutes($routes);

    public function init($routePath);
}