<?php

namespace App;

use App\Middleware\Middleware;
use App\Source\Dependencies;
use App\Routes\Routes;

class App
{
    private $app;

    public function __construct()
    {
        $setting = require __DIR__ . '/Config/settings.php';
        $app = new \Slim\App($setting);
        $this->app = $app;
        $this->dependencies();
        $this->middleware();
        $this->routes();
    }

    public function get()
    {
        return $this->app;
    }

    private function dependencies()
    {
        return new Dependencies($this->app);
    }

    private function middleware()
    {
        return new Middleware($this->app);
    }

    private function routes()
    {
        return new Routes($this->app);
    }
}
