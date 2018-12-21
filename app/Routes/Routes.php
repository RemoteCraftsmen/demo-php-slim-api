<?php

namespace App\Routes;

class Routes {
    function __construct($app) {
        return[
            new AuthRoutes($app),
            new UserRoutes($app),
            new TodoRoutes($app)
        ];
    }
}
