<?php
include __DIR__ . "/../vendor/autoload.php";

\G\Fw\App\Builder::run(
    ['debug' => true],
    __DIR__ . "/../app/conf.json",
    new \Demo\CredentialsValidator());
