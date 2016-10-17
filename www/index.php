<?php
include __DIR__ . "/../vendor/autoload.php";

use G\Fw\App\Builder;
use Demo\CredentialsValidator;
use Demo\Logger;

Builder::run(
    ['debug' => true],
    __DIR__ . "/../app/conf.json",
    new CredentialsValidator(),
    new Logger());
