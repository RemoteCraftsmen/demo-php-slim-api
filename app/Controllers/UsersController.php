<?php

namespace App\Controllers;

use App\Models\User;
use \Illuminate\Database\QueryException;
use Respect\Validation\Validator;
use Slim\Http\{Response, Request, StatusCode};

class UsersController extends Controller
{
    public function index(Request $request, Response $response)
    {
        $users = User::all();
        return $response->withJson(
            $users,
            StatusCode::HTTP_OK
        );
    }

    public function show(Request $request, Response $response, $args)
    {
        $user = User::find($args['id']);

        if (!$user) {
            return $response->withJson([
                'status' => 'Error',
                'message' => 'User does not exist'],
                StatusCode::HTTP_NOT_FOUND
            );
        }

        if (intval($args['id']) !== $request->getAttribute('loggedUserId')) {
            return $response->withJson([
                'status' => 'Error',
                'message' => 'Permission Denied'],
                StatusCode::HTTP_FORBIDDEN
            );
        }

        return $response->withJson(
            $user,
            StatusCode::HTTP_OK
        );
    }

    public function delete(Request $request, Response $response, $args)
    {
        $user = User::find($args['id']);

        if (!$user) {
            return $response->withJson([
                'status' => 'error',
                'message' => 'User does not exist'],
                StatusCode::HTTP_NOT_FOUND
            );
        }

        if (intval($args['id']) !== $request->getAttribute('loggedUserId')) {
            return $response->withJson([
                'status' => 'error',
                'message' => 'Permission Denied'],
                StatusCode::HTTP_FORBIDDEN
            );
        }

        $user->delete();

        return $response->withJson([
            'message' => 'User has been deleted'],
            StatusCode::HTTP_OK
        );
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
            return $response->withJson(
                $errors,
                StatusCode::HTTP_BAD_REQUEST
            );
        };

        $user = User::find($args['id']);

        if (!$user) {
            return $response->withJson([
                'status' => 'Error',
                'message' => 'User does not exist'],
                StatusCode::HTTP_NOT_FOUND
            );
        }

        if (intval($args['id']) !== $request->getAttribute('loggedUserId')) {
            return $response->withJson([
                'status' => 'Error',
                'message' => 'Permission Denied'],
                StatusCode::HTTP_FORBIDDEN
            );
        }

        $fieldsToUpdate = $request->getParams(['email', 'username', 'first_name', 'last_name']);
        $user->update($fieldsToUpdate);

        return $response->withJson(
            $user,
            StatusCode::HTTP_OK
        );
    }
}