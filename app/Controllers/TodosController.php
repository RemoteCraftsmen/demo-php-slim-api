<?php

namespace App\Controllers;

use App\Models\Todo;
use Respect\Validation\Validator;
use \Illuminate\Database\QueryException;
use Slim\Http\{Response, Request};


class TodosController extends Controller
{

    public function index(Request $request, Response $response)
    {
        $loggedUserId = $request->getAttribute('logged_user');
        try {
            $todos = Todo::where('user_id', $loggedUserId)->get();
            return $response->withJson($todos, 200, JSON_PRETTY_PRINT);

        } catch (QueryException $e) {
            return $response->withJson([
                'status' => 'Error',
                'message' => $e->getMessage()], 400);
        }

    }

    public function create(Request $request, Response $response)
    {

        $validation = $this->validator->validate($request, [
            'name' => Validator::notEmpty()->noWhitespace()->length(3, 30),
            'user_id' => Validator::optional(Validator::numeric())
        ]);

        if ($validation->fail()) {
            $errors = $validation->getErrors();
            return $response->withJson($errors, 401, JSON_PRETTY_PRINT);
        };

        $todoInfo = $request->getParams(['name','user_id']);
        $loggedUserId = $request->getAttribute('logged_user');

        if (!isset($todoInfo['user_id'])) {
            $todoInfo['user_id'] = $loggedUserId;
        }

        try {
            $todo = Todo::create([
                'name' => $todoInfo['name'],
                'user_id' => intval($todoInfo['user_id']),
                'creator_id' => intval($loggedUserId)
            ]);

            return $response->withJson($todo, 201, JSON_PRETTY_PRINT);

        } catch (QueryException $e) {
            return $response->withJson([
                'status' => 'Error',
                'message' => $e->getMessage()], 400);
        }
    }

    public function show(Request $request, Response $response, $args)
    {
        try {
            $todo = Todo::find($args['id']);
            $loggedUserId = $request->getAttribute('logged_user');
            if (!$todo) {
                return $response->withJson([
                    'status' => 'Error',
                    'message' => 'Item not found'], 401);
            }

            if (!$todo->user_id || $todo->user_id !== $loggedUserId) {
                return $response->withJson([
                    'status' => 'Error',
                    'message' => 'Permission Denied'], 403);
            }

            return $response->withJson($todo, 200, JSON_PRETTY_PRINT);
        } catch (QueryException $e) {
            return $response->withJson([
                'status' => 'Error',
                'message' => $e->getMessage()], 400);
        }

    }

    public function delete(Request $request, Response $response, $args)
    {
        try {
            $todo = Todo::find($args['id']);
            $loggedUserId = $request->getAttribute('logged_user');

            if (!$todo) {
                return $response->withJson([
                    'status' => 'Error',
                    'message' => 'Item not found'], 401);
            }

            if (!$todo->user_id || $todo->user_id !== $loggedUserId) {
                return $response->withJson([
                    'status' => 'Error',
                    'message' => 'Permission Denied'], 403);
            }

            $todo->delete();

            return $response->withStatus(200)->write('Item has been deleted');
        } catch (QueryException $e) {
            return $response->withJson([
                'status' => 'Error',
                'message' => $e->getMessage()], 400);
        }
    }

    public function update(Request $request, Response $response, $args)
    {

        $validation = $this->validator->validate($request, [
            'name' => Validator::notEmpty()->noWhitespace()->length(3, 30),
            'user_id' => Validator::optional(Validator::numeric())
        ]);

        if ($validation->fail()) {
            $errors = $validation->getErrors();
            return $response->withJson($errors, 401, JSON_PRETTY_PRINT);
        };

        try {
            $todo = Todo::find($args['id']);
            $loggedUserId = $request->getAttribute('logged_user');
            $fieldsToUpdate = $request->getParams(['name', 'user_id']);

            if (!$todo) {
                $fieldsToUpdate['creator_id'] = $loggedUserId;
                $todo = Todo::create($fieldsToUpdate);

                return $response->withJson($todo, 200);
            }

            if ($todo->user_id && $todo->user_id !== $loggedUserId) {
                return $response->withJson(
                    [
                        'status' => 'Error',
                        'message' => 'Permission Denied'
                    ],
                    403
                );
            }

            $todo->update($fieldsToUpdate);

            return $response->withStatus(200)->write('Todo has been updated');
        } catch (QueryException $e) {
            return $response->withJson([
                'status' => 'Error',
                'message' => $e->getMessage()], 400);
        }

    }

    public function markAsCompleted(Request $request, Response $response, $args)
    {

        $validation = $this->validator->validate($request, [
            'name' => Validator::notEmpty()->noWhitespace()->length(3, 30),
            'completed' => Validator::optional(Validator::boolVal()),
            'user_id' => Validator::optional(Validator::numeric())
        ]);

        if ($validation->fail()) {
            $errors = $validation->getErrors();
            return $response->withJson($errors, 401, JSON_PRETTY_PRINT);
        };

        try {
            $todo = Todo::find($args['id']);

            if (!$todo) {
                return $response->withJson([
                    'status' => 'Error',
                    'message' => 'Item not found'], 401);
            }

            if (!$todo->user_id || $todo->user_id !== $request->getAttribute('logged_user')) {
                return $response->withJson([
                    'status' => 'Error',
                    'message' => 'Permission Denied'], 403);
            }

            $fieldsToUpdate = $request->getParams(['name', 'completed', 'user_id']);

            $todo->update($fieldsToUpdate);

            return $response->withStatus(200)->write('Todo has been updated');

        } catch (QueryException $e) {
            return $response->withJson([
                'status' => 'Error',
                'message' => $e->getMessage()], 400);
        }

    }
}