<?php

namespace App\Tests\Setup;

use App\Models\User;
use App\Services\Auth;
use Slim\App;
use Slim\Http\Environment;
use Slim\Http\Request;

class Helper
{
    /** @var App */
    private $app;

    /** @var string */
    static private $token;

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    public function apiRequest(string $method, string $endpoint, $token = false, $postData = []): array
    {
        $envOptions = [
            'REQUEST_METHOD' => strtoupper($method),
            'REQUEST_URI' => $endpoint,
        ];

        if ($postData) {
            $envOptions['CONTENT_TYPE'] = 'application/x-www-form-urlencoded';
        }
        $env = Environment::mock($envOptions);

        $request = Request::createFromEnvironment($env)->withParsedBody($postData);

        if ($token) {
            $request = $request->withHeader('HTTP_AUTHORIZATION', 'Bearer ' . self::$token);
        }

        if ($postData) {
            $request = $request->withParsedBody($postData);
        }

        $this->app->getContainer()['request'] = $request;

        $response = $this->app->run(true);

        return [
            'code' => $response->getStatusCode(),
            'data' => json_decode($response->getBody(), true)
        ];
    }

    /**
     * @throws \Exception
     */
    static public function setToken(User $user)
    {
        self::$token = Auth::getToken($user);
    }
}
