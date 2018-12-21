<?php

namespace App\Routes;

use App\Controllers\TodosController;

class TodoRoutes {
    function __construct($app) {
        $app->group('/todo',function(){
            $this->get('/', TodosController::class . ':index');
            $this->get('/{id}', TodosController::class .':show');
            $this->post('/', TodosController::class . ':create');
            $this->put('/{id}', TodosController::class . ':update');
            $this->patch('/{id}', TodosController::class . ':markAsCompleted');
            $this->delete('/{id}', TodosController::class . ':delete');
        });
    }
}
