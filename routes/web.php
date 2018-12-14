<?php

use App\Controllers\UsersController;
use App\Controllers\TodosController;
use App\Controllers\AuthController;
use App\Middleware\CheckJWTToken;

$app->group('/auth',function() {
    $this->post('/login', AuthController::class . ':login');
    $this->post('/register', AuthController::class . ':register');
});

$app->group('/users',function(){
    $this->get('/', UsersController::class . ':index');
    $this->get('/{id}', UsersController::class .':show');
    $this->put('/{id}', UsersController::class . ':update');
    $this->delete('/{id}', UsersController::class . ':delete');
})->add(new CheckJWTToken());

$app->group('/todo',function(){
    $this->get('/', TodosController::class . ':index');
    $this->get('/{id}', TodosController::class .':show');
    $this->post('/', TodosController::class . ':create');
    $this->put('/{id}', TodosController::class . ':update');
    $this->patch('/{id}', TodosController::class . ':markAsCompleted');
    $this->delete('/{id}', TodosController::class . ':delete');
})->add(new CheckJWTToken());
