<?php

namespace App\Controllers;

use App\Models\Todo;
use Respect\Validation\Validator;
use Slim\Http\{Request, Response, StatusCode};

class TodosController extends Controller
{
    /**
     * @api {get} /todo/ Get all ToDo elements
     * @apiName GetToDoIndex
     * @apiGroup ToDo
     * @apiVersion 1.0.0
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *    {
     *        "todos": [
     *            {
     *                "id": 1,
     *                "name": "TODO",
     *                "completed": 0,
     *                "user_id": 6,
     *                "creator_id": 6,
     *                "created_at": "2018-11-27T10:30:29.700Z",
     *                "updated_at": "2018-11-27T10:30:29.700Z"
     *            }
     *        ]
     *    }
     */
    public function index(Request $request, Response $response)
    {
        $loggedUserId = $request->getAttribute('token')['loggedUserId'];
        $todos = Todo::where('user_id', $loggedUserId)->get();

        return $response->withJson(
            $todos,
            StatusCode::HTTP_OK
        );
    }

    /**
     * @api {post} /todo/ Create ToDo element
     * @apiName PostToDoStore
     * @apiGroup ToDo
     * @apiVersion 1.0.0
     *
     * @apiParam {String} name Name of that element, task
     *
     * @apiParamExample {json} Request-Example:
     *    {
     *        "name" : "NewTodo"
     *    }
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *    {
     *      "id": 4,
     *      "name": "NewTodo",
     *      "completed": 0,
     *      "user_id": 4,
     *      "creator_id": 4,
     *      "updated_at": "2019-01-16 10:16:06",
     *      "created_at": "2019-01-16 10:16:06",
     *    }
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 400 Bad Request
     *    {
     *     "name": [
     *          "Name must not be empty",
     *          "Name must have a length between 3 and 30"
     *     ]
     *    }
     */
    public function create(Request $request, Response $response)
    {
        $validation = $this->validator->validate($request, [
            'name' => Validator::notEmpty()->length(3, 30),
            'user_id' => Validator::optional(Validator::numeric())
        ]);

        if ($validation->fail()) {
            $errors = $validation->getErrors();
            return $response->withJson(
                $errors,
                StatusCode::HTTP_BAD_REQUEST
            );
        };

        $todoInfo = $request->getParams(['name', 'user_id']);
        $loggedUserId = $request->getAttribute('token')['loggedUserId'];

        if (empty($todoInfo['user_id'])) {
            $todoInfo['user_id'] = $loggedUserId;
        }

        $todo = Todo::create([
            'name' => $todoInfo['name'],
            'completed' => 0,
            'user_id' => intval($todoInfo['user_id']),
            'creator_id' => intval($loggedUserId)
        ]);

        return $response->withJson($todo, StatusCode::HTTP_CREATED);
    }

    /**
     * @api {post} /todo/:id Show ToDo element
     * @apiName GetToDoShow
     * @apiGroup ToDo
     * @apiVersion 1.0.0
     *
     * @apiParam {Number} id             ID of a ToDo List element
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *    {
     *          "id": 4,
     *          "name": "NewTodo",
     *          "completed": 0,
     *          "user_id": 4,
     *          "creator_id": 4,
     *          "updated_at": "2019-01-16 10:16:06",
     *          "created_at": "2019-01-16 10:16:06",
     *    }
     *
     *
     * @apiError (404) Not Found    The <code>id</code> of the ToDo element was not found.
     */
    public function show(Request $request, Response $response, $args)
    {
        $todo = Todo::find($args['id']);
        $loggedUserId = $request->getAttribute('token')['loggedUserId'];

        if (!$todo) {
            return $response->withJson([
                'status' => 'error',
                'message' => 'Item not found'],
                StatusCode::HTTP_NOT_FOUND
            );
        }

        if (!$todo->user_id || $todo->user_id !== $loggedUserId) {
            return $response->withJson([
                'status' => 'error',
                'message' => 'Permission Denied'],
                StatusCode::HTTP_FORBIDDEN
            );
        }

        return $response->withJson(
            $todo,
            StatusCode::HTTP_OK
        );
    }

    /**
     * @api {delete} /todo/:id Delete ToDo element
     * @apiName DeleteToDoDestroy
     * @apiGroup ToDo
     * @apiVersion 1.0.0
     *
     * @apiParam {Number} id ID of a ToDo List element
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 Content
     *       {
     *          "message": "Item has been deleted"
     *       }
     *
     * @apiError NotFound     The <code>id</code> of the ToDo element was not found.
     * @apiError Forbidden    ToDo element belongs to other User
     * @apiError BadRequest
     */
    public function delete(Request $request, Response $response, $args)
    {
        $todo = Todo::find($args['id']);
        $loggedUserId = $request->getAttribute('token')['loggedUserId'];

        if (!$todo) {
            return $response->withJson([
                'status' => 'error',
                'message' => 'Item not found'],
                StatusCode::HTTP_NOT_FOUND
            );
        }

        if (!$todo->user_id || $todo->user_id !== $loggedUserId) {
            return $response->withJson([
                'status' => 'error',
                'message' => 'Permission Denied'],
                StatusCode::HTTP_FORBIDDEN
            );
        }

        $todo->delete();

        return $response->withJson([
            'message' => 'Item has been deleted'],
            StatusCode::HTTP_OK
        );
    }

    /**
     * @api {put} /todos/:id Update/Create {PUT} ToDo element
     * @apiName PutToDoPut
     * @apiGroup ToDo
     * @apiVersion 1.0.0
     *
     * @apiDescription With this method we can not only update elements, but also create them, depends on :id pamaretr.
     *  If :id already exist in db table we are updating, if not we are creating element
     *
     * @apiParam {Number} id
     * @apiParam {String} name
     * @apiParam {String} user_id
     *
     * @apiSuccessExample {json} Succes : Creating new element - only when :id does not exist in table
     *     HTTP/1.1 200 OK
     *    {
     *          "id": 2,
     *          "name": "TodoUpdated",
     *          "completed": 0,
     *          "user_id": "20",
     *          "creator_id": 4,
     *          "created_at": "2019-01-16 10:13:40",
     *          "updated_at": "2019-01-16 10:22:49"
     *    }
     *
     * @apiError BadRequest    The <code>id</code> of the ToDo element was not found, <code>id</code> does not exist in table ToDo and parametr "name" is not specified
     * @apiError Forbidden     ToDo element belongs to other User
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 400 NotFound
     *      {
     *          "name": [
     *              "Name must not be empty",
     *              "Name must have a length between 3 and 30"
     *          ]
     *      }
     */
    public function update(Request $request, Response $response, $args)
    {
        $validation = $this->validator->validate($request, [
            'name' => Validator::notEmpty()->length(3, 30),
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
        $loggedUserId = $request->getAttribute('token')['loggedUserId'];
        $fieldsToUpdate = $request->getParams(['name', 'user_id']);

        if (!$todo) {
            $fieldsToUpdate['creator_id'] = $loggedUserId;
            $fieldsToUpdate['user_id'] = $loggedUserId;
            $todo = Todo::create($fieldsToUpdate);

            return $response->withJson(
                $todo,
                StatusCode::HTTP_OK
            );
        }

        if ($todo->user_id && $todo->user_id !== $loggedUserId) {
            return $response->withJson(
                [
                    'status' => 'error',
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

    /**
     * @api {patch} /todo/:id Update {PATCH} ToDo element / Mark As Completed
     * @apiName PatchToDoPatch
     * @apiGroup ToDo
     * @apiVersion 1.0.0
     *
     * @apiDescription Very similar to PUT Method. THe difference is that we can mark ToDo element as completed or not completed (change "completed" field in Table to true or false).
     *
     * @apiParam {Number} id           ID of a ToDo List element
     * @apiParam {String} name         New name of a ToDo List element. When updating existing element, this parameter is optional
     * @apiParam {Number} completed      State of completion
     * @apiParam {String} user_id      Id of owner
     *
     * @apiParamExample {json} Request-Example:
     *   {
     *    "name" : "krarkakrkar11112",
     *    "user_id" : 10,
     *    "completed": 1
     *   }
     *
     * @apiError BadRequest    The <code>id</code> of the ToDo element was not found, <code>id</code> does not exist in table ToDo and parametr "name" is not specified
     * @apiError Forbidden     ToDo element belongs to other User
     */
    public function markAsCompleted(Request $request, Response $response, $args)
    {
        $validation = $this->validator->validate($request, [
            'name' => Validator::notEmpty()->length(3, 30),
            'completed' => Validator::optional(Validator::numeric()),
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
        $loggedUserId = $request->getAttribute('token')['loggedUserId'];

        if (!$todo) {
            return $response->withJson([
                'status' => 'error',
                'message' => 'Item not found'],
                StatusCode::HTTP_NOT_FOUND
            );
        }

        if (!$todo->user_id || $todo->user_id !== $loggedUserId) {
            return $response->withJson([
                'status' => 'error',
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
