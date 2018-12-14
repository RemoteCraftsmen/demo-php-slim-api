<?php

namespace App\Controllers;

use App\Models\Todo;
use Illuminate\Database\QueryException;
use Respect\Validation\Validator;
use Slim\Http\{Request, Response, StatusCode};

class TodosController extends Controller
{

    public function index(Request $request, Response $response)
    {
        $loggedUserId = $request->getAttribute('loggedUserId');
        $todos = Todo::where('user_id', $loggedUserId)->get();
        return $response->withJson(
            $todos,
            StatusCode::HTTP_OK
        );
    }

    public function create(Request $request, Response $response)
    {

        $validation = $this->validator->validate($request, [
            'name' => Validator::notEmpty()->noWhitespace()->length(3, 30),
            'user_id' => Validator::optional(Validator::numeric())
        ]);

        if ($validation->fail()) {
            $errors = $validation->getErrors();
            return $response->withJson(
                $errors,
                StatusCode::HTTP_BAD_REQUEST
            );
        };

        $todoInfo = $request->getParams(['name','user_id']);
        $loggedUserId = $request->getAttribute('loggedUserId');

        if (empty($todoInfo['user_id'])) {
            $todoInfo['user_id'] = $loggedUserId;
        }

        $todo = Todo::create([
            'name' => $todoInfo['name'],
            'user_id' => intval($todoInfo['user_id']),
            'creator_id' => intval($loggedUserId)
        ]);

        return $response->withJson($todo, StatusCode::HTTP_CREATED);
    }

    public function show(Request $request, Response $response, $args)
    {
        $todo = Todo::find($args['id']);
        $loggedUserId = $request->getAttribute('loggedUserId');
        if (!$todo) {
            return $response->withJson([
                'status' => 'Error',
                'message' => 'Item not found'],
                StatusCode::HTTP_NOT_FOUND
            );
        }

        if (!$todo->user_id || $todo->user_id !== $loggedUserId) {
            return $response->withJson([
                'status' => 'Error',
                'message' => 'Permission Denied'],
                StatusCode::HTTP_FORBIDDEN
            );
        }

        return $response->withJson(
            $todo,
            StatusCode::HTTP_OK
        );
    }

    public function delete(Request $request, Response $response, $args)
    {
        $todo = Todo::find($args['id']);
        $loggedUserId = $request->getAttribute('loggedUserId');

        if (!$todo) {
            return $response->withJson([
                'status' => 'Error',
                'message' => 'Item not found'],
                StatusCode::HTTP_NOT_FOUND
            );
        }

        if (!$todo->user_id || $todo->user_id !== $loggedUserId) {
            return $response->withJson([
                'status' => 'Error',
                'message' => 'Permission Denied'],
                StatusCode::HTTP_FORBIDDEN
            );
        }

        $todo->delete();
        return $response->withStatus(StatusCode::HTTP_OK)->write('Item has been deleted');
    }

    public function update(Request $request, Response $response, $args)
    {

        $validation = $this->validator->validate($request, [
            'name' => Validator::notEmpty()->noWhitespace()->length(3, 30),
            'user_id' => Validator::optional(Validator::numeric())
        ]);

        if ($validation->fail()) {
            $errors = $validation->getErrors();
            return $response->withJson(
                $errors,
                StatusCode::HTTP_BAD_REQUEST
            );
        };

        $todo = Todo::find($args['id']);
        $loggedUserId = $request->getAttribute('loggedUserId');
        $fieldsToUpdate = $request->getParams(['name', 'user_id']);

        if (!$todo) {
            $fieldsToUpdate['creator_id'] = $loggedUserId;
            $todo = Todo::create($fieldsToUpdate);

            return $response->withJson(
                $todo,
                StatusCode::HTTP_OK
            );
        }

        if ($todo->user_id && $todo->user_id !== $loggedUserId) {
            return $response->withJson(
                [
                    'status' => 'Error',
                    'message' => 'Permission Denied'
                ],
                StatusCode::HTTP_FORBIDDEN
            );
        }

        $todo->update($fieldsToUpdate);
        return $response->withJson(
            $todo,
            StatusCode::HTTP_OK
        );
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
            return $response->withJson(
                $errors,
                StatusCode::HTTP_BAD_REQUEST
            );
        };

        $todo = Todo::find($args['id']);

        if (!$todo) {
            return $response->withJson([
                'status' => 'Error',
                'message' => 'Item not found'],
                StatusCode::HTTP_NOT_FOUND
            );
        }

        if (!$todo->user_id || $todo->user_id !== $request->getAttribute('loggedUserId')) {
            return $response->withJson([
                'status' => 'Error',
                'message' => 'Permission Denied'],
                StatusCode::HTTP_FORBIDDEN
            );
        }

        $fieldsToUpdate = $request->getParams(['name', 'completed', 'user_id']);
        $todo->update($fieldsToUpdate);
        return $response->withJson(
            $todo,
            StatusCode::HTTP_OK
        );
    }
}