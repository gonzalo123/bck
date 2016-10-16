<?php
namespace G\Fw;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

abstract class BuilderAbstract
{
    protected $request;
    protected $class;
    protected $method;
    protected $type;
    protected $subType;
    protected $app;
    protected $routes = [];

    public function __construct(Request $request, Application $app)
    {
        $this->app     = $app;
        $this->request = $request;
    }

    public function init($routePath)
    {
        $method = strtolower($this->request->getMethod());
        $key    = "{$method}::" . $routePath;
        if (array_key_exists($key, $this->routes)) {
            $info          = explode("::", $this->routes[$key]);
            $this->class   = $info[0];
            $this->method  = $info[1];
            $this->type    = isset($info[2]) ? $info[2] : 'json';
            $this->subType = isset($info[3]) ? $info[3] : null;
        } else {
            throw new NotFoundHttpException();
        }
    }

    public function setRoutes($routes)
    {
        $this->routes = $routes;
    }

    protected function getUser()
    {
        return $this->app['user'];
    }

    protected function getRequest()
    {
        return $this->request;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getSubType()
    {
        return $this->subType;
    }

    public function preFetch()
    {
    }

    public function fetch()
    {
        $obj = $this->getInstance($this->class);
        $this->preFetch();
        $out = call_user_func_array([$obj, $this->method], $this->getDependencies($this->class, $this->method));

        return $out;
    }

    public function finish(Request $request, Response $response)
    {
    }

    private function getInstance($class)
    {
        $metaClass = new \ReflectionClass($class);

        return $metaClass->hasMethod('__construct') ?
            $metaClass->newInstanceArgs($this->getDependencies($class, '__construct')) :

            new $class;
    }

    private function getDependencies($class, $methodName)
    {
        $method       = new \ReflectionMethod($class, $methodName);
        $dependencies = [];
        foreach ($method->getParameters() as $param) {
            $parameterName = $param->getName();
            if ($parameterName == '_user') {
                $dependencies[$parameterName] = $this->app['user'];
            } else {
                $dependencies[$parameterName] = $this->request->get($parameterName);
            }
        }

        return $dependencies;
    }
}