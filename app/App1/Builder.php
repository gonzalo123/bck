<?php
namespace Demo\App1;

use G\Fw\BuilderAbstract;
use G\Fw\BuilderIface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class Builder extends BuilderAbstract implements BuilderIface
{
    private $validUsers = ['gonzalo'];

    public function preFetch()
    {
        if (!in_array($this->getUser(), $this->validUsers)) {
            throw new AccessDeniedHttpException("Not valid user");
        }
    }

    public function finish(Request $request, Response $response)
    {
        // log request
    }
}