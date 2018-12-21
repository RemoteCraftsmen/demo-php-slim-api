<?php

namespace App\Source;

use App\Validation\Validator;
use App\Handlers\ErrorLogger;

class Dependencies
{
    private $container;

    function __construct($app)
    {
        $this->container = $app->getContainer();;
        $this->errorLogger();
        $this->databaseEloquent();
        $this->validator();
        $this->errorHandler();
    }

    function errorLogger()
    {
        $this->container['logger'] = function ($c) {
            $settings = $c->get('settings')['logger'];
            $logger = new Monolog\Logger($settings['name']);
            $logger->pushProcessor(new Monolog\Processor\UidProcessor());
            $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
            return $logger;
        };
    }

    function databaseEloquent()
    {
        $capsule = new \Illuminate\Database\Capsule\Manager;
        $capsule->addConnection($this->container['settings']['db']);
        $capsule->setAsGlobal();
        $capsule->bootEloquent();
        $this->container['db'] = function ($container) use ($capsule) {
            return $capsule;
        };
    }

    function validator()
    {
        $this->container['validator'] = function ($container) {
            return new Validator;
        };
    }


    function errorHandler()
    {
        $this->container['errorHandler'] = function ($container) {
            return function ($request, $response, $error) use ($container) {
                if ($error instanceof Illuminate\Database\QueryException) {
                    if (getenv('APP_ENV') == 'development') {
                        $obj = new ErrorLogger($container['logger']);
                        $obj($request, $response, $error);
                    }

                    return $response->withJson([
                        'status' => 'error',
                        'message' => (getenv('APP_ENV') == 'development') ? $error->getMessage() : 'Internal Server Error'],
                        StatusCode::HTTP_INTERNAL_SERVER_ERROR
                    );
                }
            };
        };
    }

}