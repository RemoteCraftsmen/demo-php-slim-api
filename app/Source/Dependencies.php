<?php

namespace App\Source;

use App\Validation\Validator;
use App\Handlers\ErrorLogger;
use Slim\App;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\StatusCode;

class Dependencies
{
    private $container;

    function __construct(App $app)
    {
        $this->container = $app->getContainer();;
        $this->errorLogger();
        $this->databaseEloquent();
        $this->validator();
        $this->errorHandler();
    }

    function errorLogger()
    {
        $this->container['logger'] = function (Container $c) {
            $settings = $c->get('settings')['logger'];
            $logger = new \Monolog\Logger($settings['name']);
            $logger->pushProcessor(new \Monolog\Processor\UidProcessor());
            $logger->pushHandler(new \Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
            return $logger;
        };
    }

    function databaseEloquent()
    {
        $capsule = new \Illuminate\Database\Capsule\Manager;
        $capsule->addConnection($this->container['settings']['db']);
        $capsule->setAsGlobal();
        $capsule->bootEloquent();
        $this->container['db'] = function (Container $container) use ($capsule) {
            return $capsule;
        };
    }

    function validator()
    {
        $this->container['validator'] = function (Container $container) {
            return new Validator;
        };
    }

    function errorHandler()
    {
        $this->container['errorHandler'] = function ($container) {
            return function (Request $request, Response $response, $error) use ($container) {
                if ($error instanceof \Illuminate\Database\QueryException) {
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
