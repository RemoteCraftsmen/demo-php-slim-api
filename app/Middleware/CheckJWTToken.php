<?php

namespace App\Middleware;

use Firebase\JWT\JWT;
use Slim\Http\{Response, Request};

class CheckJWTToken
{
    public function __invoke(Request $request, Response $response, $next)
    {
        $token = $request->getServerParams()['HTTP_AUTHORIZATION'];

        $token = str_replace('Bearer ', '', $token);

        if (!$token) {
            return $response->withJson(
                [
                    'auth'      => 'false',
                    'status'    => 'Error',
                    'message'   => 'No token provided'
                ],
                403
            );
        }

        $decodedToken = JWT::decode($token, $_ENV['JWT_SECRET'], array('HS256'));

        if(!isset($decodedToken->logged_user)){
            return $response->withJson(
                [
                    'auth'      => 'false',
                    'status'    => 'Error',
                    'message'   => 'Failed to authenticate token.'
                ],
                403
            );
        }

        $request = $request->withAttribute('logged_user', $decodedToken->logged_user);

        return $next($request, $response);
    }

}