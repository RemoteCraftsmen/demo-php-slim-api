<?php

namespace App\Tests\Setup;

use App\Services\Auth;
use Slim\Http\Environment;
use Slim\Http\Request;

class Helper
{
    private $app;
    static private $token;

    public function __construct($app)
    {
        $this->app = $app;
    }

    // Simulates queries to our REST API using mock environment
    public function apiTest($method, $endpoint, $token = false, $postData = [])
    {
        $envOptions = [
            'REQUEST_METHOD' => strtoupper($method),
            'REQUEST_URI' => $endpoint,
        ];
        if ($postData) {
            $envOptions['CONTENT_TYPE'] = 'application/x-www-form-urlencoded';
        }
        //Authorization: Bearer
        $env = Environment::mock($envOptions);
        if ($token) {
            $request = $postData ? Request::createFromEnvironment($env)->withHeader('HTTP_AUTHORIZATION', 'Bearer ' . self::$token)->withParsedBody($postData) : Request::createFromEnvironment($env)->withHeader('HTTP_AUTHORIZATION', 'Bearer ' . self::$token);
        } else {
            $request = $postData ? Request::createFromEnvironment($env)->withParsedBody($postData) : Request::createFromEnvironment($env);
        }
        $this->app->getContainer()['request'] = $request;
        $response = $this->app->run(true);
        return [
            'code' => $response->getStatusCode(),
            'data' => json_decode($response->getBody(), true)
        ];
    }

    static public function setToken($user)
    {
        self::$token = Auth::getToken($user);
    }

}
