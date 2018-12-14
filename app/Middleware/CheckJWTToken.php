<?php

namespace App\Middleware;

use Firebase\JWT\JWT;
use Slim\Http\{Response, Request, StatusCode};

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
                StatusCode::HTTP_FORBIDDEN
            );
        }

        $decodedToken = JWT::decode($token, $_ENV['JWT_SECRET'], array('HS256'));

        if(empty($decodedToken->loggedUserId)){
            return $response->withJson(
                [
                    'auth'      => 'false',
                    'status'    => 'error',
                    'message'   => 'Failed to authenticate token.'
                ],
                StatusCode::HTTP_FORBIDDEN
            );
        }

        $request = $request->withAttribute('loggedUserId', $decodedToken->loggedUserId);

        return $next($request, $response);
    }

}