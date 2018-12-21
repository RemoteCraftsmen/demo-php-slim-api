<?php

namespace App\Controllers;

use App\Models\User;
use App\Services\Auth;
use Respect\Validation\Validator;
use Slim\Http\{Request, Response, StatusCode};

class AuthController extends Controller
{
    public function login(Request $request, Response $response)
    {
        $userInfo = $request->getParams();

        $user = User::where('email', $userInfo['email'])->first();

        if (!$user) {
            return $response->withJson([
                'status' => 'error',
                'auth' => false,
                'message' => 'User does not exist'], StatusCode::HTTP_NOT_FOUND);
        }

        if (!Auth::checkPasswords($userInfo['password'], $user->password)) {
            return $response->withJson([
                'status' => 'error',
                'auth' => false],
                StatusCode::HTTP_UNAUTHORIZED);
        }

        $token = Auth::getToken($user);

        return $response->withJson(
            [
                "token" => $token,
                "user" => $user
            ],
            StatusCode::HTTP_OK
        );
    }

    public function register(Request $request, Response $response)
    {
        $validation = $this->validator->validate($request, [
            'email' => Validator::noWhitespace()->notEmpty()->email()->length(3, 100),
            'username' => Validator::notEmpty()->length(3, 30),
            'password' => Validator::noWhitespace()->notEmpty()->length(5, 100),
            'first_name' => Validator::notEmpty()->length(3, 30),
            'last_name' => Validator::notEmpty()->length(3, 30),
        ]);

        if ($validation->fail()) {
            $errors = $validation->getErrors();
            return $response->withJson(
                $errors,
                StatusCode::HTTP_BAD_REQUEST
            );
        };

        $userInfo = $request->getParams();

        if (User::where('email', $userInfo['email'])->first()) {
            return $response->withJson([
                'status' => 'error',
                'message' => 'The user with such email already exist'],
                StatusCode::HTTP_CONFLICT

            );
        }

        $user = User::create([
            'email' => $userInfo['email'],
            'username' => $userInfo['username'],
            'password' => password_hash($userInfo['password'], PASSWORD_BCRYPT),
            'first_name' => $userInfo['first_name'],
            'last_name' => $userInfo['last_name'],
        ]);

        $token = Auth::getToken($user);
        
        return $response->withJson(
            [
                "token" => $token,
                "user" => $user
            ],
            StatusCode::HTTP_OK
        );
    }
}
