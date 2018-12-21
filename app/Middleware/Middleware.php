<?php

namespace App\Middleware;

use Tuupola\Middleware\JwtAuthentication;

class Middleware
{
    private $app;
    private $container;

    function __construct($app)
    {
        $this->app = $app;
        $this->container = $app->getContainer();
        $this->jwtAuthentication();
    }

    function jwtAuthentication()
    {
        $jwtSettings = $this->container->get('settings')['jwt'];
        $this->app->add(new JwtAuthentication($jwtSettings));
    }
}