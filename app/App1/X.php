<?php
namespace Demo\App1;

class X
{
    private $user;

    public function __construct($_user)
    {
        $this->user = $_user;
    }

    public function getData()
    {
        return [
            'data' => [
                ['name' => 'Gonzalo', 'surname' => $this->user],
                ['name' => 'Peter', 'surname' => 'Parker'],
            ]
        ];
    }

    public function hello($name)
    {
        return "Hello {$name}";
    }

    public function getImg()
    {
        return "rawImage";
    }
}