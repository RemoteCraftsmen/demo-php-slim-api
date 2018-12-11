<?php

namespace App\Controllers;

use App\Models\User;
use App\Services\Auth;
use \Illuminate\Database\QueryException;
use Respect\Validation\Validator;
use Slim\Http\{Response, Request};

class UsersController extends Controller
{
    public function index(Request $request, Response $response)
    {
        try {
            $users = User::all();
            // $users = User::all()->toJson()... this method should convert Eloquent object to json, but it lefts some weird signs
            return $response->withJson($users, 200, JSON_PRETTY_PRINT);
        } catch (QueryException $e) {
            return $response->withJson([
                'status' => 'Error',
                'message' => $e->getMessage()], 400);
        }

    }

    public function create(Request $request, Response $response)
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
            return $response->withJson($errors, 400, JSON_PRETTY_PRINT);
        };

        $userInfo = $request->getParams();

        if (User::where('email', $userInfo['email'])->first()) {
            return $response->withStatus(409)->write('error : This email already exist in Data Base');
        }

        try {

            $user = User::create([
                'email' => $userInfo['email'],
                'username' => $userInfo['username'],
                'password' => password_hash($userInfo['password'], PASSWORD_BCRYPT),
                'first_name' => $userInfo['first_name'],
                'last_name' => $userInfo['last_name'],

            ]);

            $token = Auth::getToken($user);

            return $response->withJson(["token" => $token, $user], 200, JSON_PRETTY_PRINT);

        } catch (QueryException $e) {
            return $response->withJson([
                'status' => 'Error',
                'message' => $e->getMessage()], 400);
        }

    }


    public function show(Request $request, Response $response, $args)
    {

        try {
            $user = User::find($args['id']);

            if (!$user) {
                return $response->withStatus(404)->withJson([
                    'status' => 'Error',
                    'message' => 'User does not exist']);
            }

            if (intval($args['id']) !== $request->getAttribute('logged_user')) {
                return $response->withStatus(403)->write('Permission Denied');
            }

            return $response->withJson($user, 200, JSON_PRETTY_PRINT);

        } catch (QueryException $e) {
            return $response->withJson([
                'status' => 'Error',
                'message' => $e->getMessage()], 400);
        }

    }

    public function delete(Request $request, Response $response, $args)
    {

        try {
            $user = User::find($args['id']);

            if (!$user) {
                return $response->withStatus(404)->withJson([
                    'status' => 'Error',
                    'message' => 'User does not exist']);
            }

            if (intval($args['id']) !== $request->getAttribute('logged_user')) {
                return $response->withStatus(403)->write('Permission Denied');
            }

            $user->delete();
            return $response->withStatus(200)->write('User has been deleted');

        } catch (QueryException $e) {
            return $response->withJson([
                'status' => 'Error',
                'message' => $e->getMessage()], 400);
        }
    }

    public function update(Request $request, Response $response, $args)
    {

        $validation = $this->validator->validate($request, [
            'email' => Validator::noWhitespace()->notEmpty()->email()->length(3, 100),
            'username' => Validator::notEmpty()->length(3, 30),
            'first_name' => Validator::notEmpty()->length(3, 30),
            'last_name' => Validator::notEmpty()->length(3, 30),
        ]);

        if ($validation->fail()) {
            $errors = $validation->getErrors();
            return $response->withJson($errors, 401, JSON_PRETTY_PRINT);
        };
        try {
            $user = User::find($args['id']);

            if (!$user) {
                return $response->withStatus(404)->withJson([
                    'status' => 'Error',
                    'message' => 'User does not exist']);
            }

            if (intval($args['id']) !== $request->getAttribute('logged_user')) {
                return $response->withStatus(403)->write('Permission Denied');
            }

            //We can define in getParams which fields user is able to update
            $fieldsToUpdate = $request->getParams(['email', 'username', 'first_name', 'last_name']);

            $user->update($fieldsToUpdate);
        } catch (QueryException $e) {
            return $response->withJson([
                'status' => 'Error',
                'message' => $e->getMessage()], 400);
        }
        return $response->withStatus(200)->write('User has been updated');
    }

}