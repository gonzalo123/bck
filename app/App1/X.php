<?php
namespace Demo\App1;

class X
{
    public function getData($_user)
    {
        return [
            'data' => [
                ['name' => 'Gonzalo', 'surname' => $_user],
                ['name' => 'Peter', 'surname' => 'Parker'],
            ]
        ];
    }

    public function getImg()
    {
        return "rawImage";
    }
}