<?php

namespace App\Routes;

use App\Controllers\UsersController;

class UserRoutes {
    function __construct($app) {
        $app->group('/users',function(){
            $this->get('/', UsersController::class . ':index');
            $this->get('/{id}', UsersController::class .':show');
            $this->put('/{id}', UsersController::class . ':update');
            $this->delete('/{id}', UsersController::class . ':delete');
        });
    }
}
