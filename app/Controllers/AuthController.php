<?php

namespace App\Controllers;

use App\Models\User;
use App\Services\Auth;
use Slim\Http\{Response, Request};
use \Illuminate\Database\QueryException;


class AuthController extends Controller
{
    public function login(Request $request, Response $response)
    {

        $userInfo = $request->getParams();

        try {
            $user = User::where('email', $userInfo['email'])->first();

            if (!$user->email) {
                return $response->withJson([
                    'status' => 'Error',
                    'auth' => false,
                    'message' => 'User does not exist'], 404);
            }

            if (Auth::checkPasswords($userInfo['password'], $user->password)) {
                $token = Auth::getToken($user);
                return $response->withJson(["token" => $token, $user], 200, JSON_PRETTY_PRINT);
            }

            return $response->withJson([
                'status' => 'Error',
                'auth' => false], 401);

        } catch (QueryException $e) {
            return $response->withStatus($e->getCode())->withJson([
                'status' => 'Error',
                'message' => $e->getMessage()]);
        }
    }

}