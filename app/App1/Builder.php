<?php
namespace Demo\App1;

use G\Bck\BuilderAbstract;
use G\Bck\BuilderIface;
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
}