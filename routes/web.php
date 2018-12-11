<?php
/**
 * Created by PhpStorm.
 * User: kamil
 * Date: 2018-12-07
 * Time: 12:03
 */

use App\Controllers\UsersController;
use App\Controllers\TodosController;
use App\Controllers\AuthController;
use App\Middleware\CheckJWTToken;

$app->post('/auth/login', AuthController::class . ':login');

$app->group('/users',function(){
    $this->get('/', UsersController::class . ':index')->add(new CheckJWTToken());
    $this->get('/{id}', UsersController::class .':show')->add(new CheckJWTToken());
    $this->post('/', UsersController::class . ':create');
    $this->put('/{id}', UsersController::class . ':update')->add(new CheckJWTToken());
    $this->delete('/{id}', UsersController::class . ':delete')->add(new CheckJWTToken());
});

$app->group('/todo',function(){
    $this->get('/', TodosController::class . ':index');
    $this->get('/{id}', TodosController::class .':show');
    $this->post('/', TodosController::class . ':create');
    $this->put('/{id}', TodosController::class . ':update');
    $this->patch('/{id}', TodosController::class . ':markAsCompleted');
    $this->delete('/{id}', TodosController::class . ':delete');
})->add(new CheckJWTToken());
