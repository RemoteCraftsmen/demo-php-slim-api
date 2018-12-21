<?php

namespace App\Routes;

use App\Controllers\AuthController;

class AuthRoutes {
    function __construct($app) {
        $app->group('/auth',function() {
            $this->post('/register', AuthController::class . ':register');
            $this->post('/login', AuthController::class . ':login');
        });
    }
}