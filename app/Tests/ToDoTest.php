<?php

namespace App\Tests;

use App\Models\Todo;
use App\Tests\Setup\Bootstrap;
use PHPUnit\Framework\TestCase;

class ToDoTest extends TestCase
{

    static private $helper;

    public static function setUpBeforeClass()
    {
        self::$helper = Bootstrap::getApp();
    }

    protected function tearDown()
    {
        Bootstrap::clearDatabase();
    }

    public function testTryToCreateTodoItem()
    {
        $loggedUser = Bootstrap::createLoggedUser();
        $newTodo = [
            'name' => 'new ToDo'
        ];

        $response = self::$helper->apiTest('post', '/todo/', true, $newTodo);
        $todo = Todo::find($response['data']['id']);

        $this->assertSame($response['code'], 201);
        $this->assertEquals($response['data']['name'], $newTodo['name']);
        $this->assertEquals($response['data']['user_id'], $loggedUser->id);
        $this->assertTrue(isset($todo));
    }

    public function testTryToCreateTodoItemWithTooLongName()
    {
        $newTodo = [
            'name' => str_repeat("a", 50)
        ];

        $response = self::$helper->apiTest('post', '/todo/', true, $newTodo);
        $todo = Todo::find($response['data']['id']);

        $this->assertSame($response['code'], 400);
        $this->assertNotEquals($response['data']['name'], $newTodo['name']);
        $this->assertEquals($response['data']['name'][0], 'Name must have a length between 3 and 30');
        $this->assertFalse(isset($todo));
    }

    public function testTryToShowAllTodoItemsBelongingToLoggedUser()
    {
        $loggedUser = Bootstrap::createLoggedUser();
        Bootstrap::createLoggedUserTodoCollection($loggedUser->id);

        $response = self::$helper->apiTest('get', '/todo/', true);
        $todos = Todo::where('user_id', $loggedUser->id)->get();

        $this->assertSame($response['code'], 200);
        $this->assertSame(count($response['data']), count($todos));
    }

    public function testTryToShowSingleTodoItemBelongingToLoggedUser()
    {
        $loggedUser = Bootstrap::createLoggedUser();
        $randomTodoId = Bootstrap::getRandomLoggedUserTodoId($loggedUser->id);
        $todo = Todo::where('user_id', $loggedUser->id)->first();

        $response = self::$helper->apiTest('get', '/todo/' . $randomTodoId, true);

        $this->assertSame($response['code'], 200);
        $this->assertEquals($response['data']['user_id'], $loggedUser->id);
        $this->assertEquals($response['data']['creator_id'], $loggedUser->id);
    }

    public function testTryToShowSingleTodoItemNotBelongingToLoggedUser()
    {
        $usersId = Bootstrap::getUsersIdCollection();
        $loggedUser = Bootstrap::createLoggedUser();
        Bootstrap::getRandomTodos($usersId);
        $todo = Todo::where('user_id', '!=', $loggedUser->id)->first();

        $response = self::$helper->apiTest('get', '/todo/' . $todo->id, true);

        $this->assertSame($response['code'], 403);
        $this->assertEquals($response['data']['message'], 'Permission Denied');
    }

    public function testTryToShowSingleTodoItemWhichDoesNotExist()
    {
        Bootstrap::createLoggedUser();
        $id = 999999999;

        $response = self::$helper->apiTest('get', '/todo/' . $id, true);

        $this->assertSame($response['code'], 404);
        $this->assertEquals($response['data']['message'], 'Item not found');
    }

    public function testTryToUpdateTodoItemBelongingToLoggedUser()
    {
        $loggedUser = Bootstrap::createLoggedUser();
        $todoId = Bootstrap::getRandomLoggedUserTodoId($loggedUser->id);
        $newTodo = [
            "name" => "new ToDo",
            "user_id" => 1
        ];

        $response = self::$helper->apiTest('put', '/todo/' . $todoId, true, $newTodo);

        $this->assertSame($response['code'], 200);
        $this->assertEquals($response['data']['name'], $newTodo['name']);
        $this->assertEquals($response['data']['id'], $todoId);
        $this->assertEquals($response['data']['creator_id'], $loggedUser->id);
        $this->assertNotEquals($response['data']['user_id'], $loggedUser->id);
    }

    public function testTryToUpdateTodoItemWithTooShortName()
    {
        $loggedUser = Bootstrap::createLoggedUser();
        $todoId = Bootstrap::getRandomLoggedUserTodoId($loggedUser->id);
        $newTodo = [
            "name" => "n",
            "user_id" => 1
        ];

        $response = self::$helper->apiTest('put', '/todo/' . $todoId, true, $newTodo);

        $this->assertSame($response['code'], 400);
        $this->assertEquals($response['data']['name'][0], 'Name must have a length between 3 and 30');
    }

    public function testTryToCreateTodoItemIfIdPassedInRouteDoesNotExist()
    {
        Bootstrap::createLoggedUser();
        $newTodo = [
            "name" => "aaaaaaaaaaaaaaaaaaaa",
            "user_id" => 1
        ];
        $id = 99999;

        $response = self::$helper->apiTest('put', '/todo/' . $id, true, $newTodo);
        $todo = Todo::where('name', $newTodo['name'])->first();

        $this->assertSame($response['code'], 200);
        $this->assertTrue(isset($todo));
    }

    public function testTryToUpdateTodoItemWhichDoesNotBelongsToLoggedUser()
    {
        $usersId = Bootstrap::getUsersIdCollection();
        $todoId = Bootstrap::getRandomTodoId($usersId);
        Bootstrap::createLoggedUser();
        $newTodo = [
            "name" => "new ToDo",
            "user_id" => 1
        ];

        $response = self::$helper->apiTest('put', '/todo/' . $todoId, true, $newTodo);

        $this->assertSame($response['code'], 403);
        $this->assertEquals($response['data']['message'], 'Permission Denied');
    }

    public function testTryToMarkAsCompletedTodoItemBelongingToLoggedUser()
    {
        $loggedUser = Bootstrap::createLoggedUser();
        $todoId = Bootstrap::getRandomLoggedUserTodoId($loggedUser->id);
        $newTodo = [
            "name" => "new ToDo",
            "user_id" => 1
        ];

        $response = self::$helper->apiTest('patch', '/todo/' . $todoId, true, $newTodo);

        $this->assertSame($response['code'], 200);
        $this->assertEquals($response['data']['id'], $todoId);
        $this->assertEquals($response['data']['user_id'], $newTodo['user_id']);
        $this->assertEquals($response['data']['creator_id'], $loggedUser->id);
        $this->assertEquals($response['data']['name'], $newTodo['name']);
    }

    public function testTryToMarkAsCompletedTodoItemBelongingToAnotherUser()
    {
        $usersId = Bootstrap::getUsersIdCollection();
        $todoId = Bootstrap::getRandomTodoId($usersId);
        Bootstrap::createLoggedUser();
        $newTodo = [
            "name" => "new ToDo",
            "user_id" => 1
        ];

        $response = self::$helper->apiTest('patch', '/todo/' . $todoId, true, $newTodo);

        $this->assertSame($response['code'], 403);
        $this->assertEquals($response['data']['message'], 'Permission Denied');
    }

    public function testTryToMarkAsCompletedTodoItemWhichDoesNotExist()
    {
        $newTodo = [
            "name" => "new ToDo",
            "completed" => true,
            "user_id" => 1
        ];
        $id = 99999;

        $response = self::$helper->apiTest('patch', '/todo/' . $id, true, $newTodo);

        $this->assertSame($response['code'], 404);
        $this->assertEquals($response['data']['message'], 'Item not found');
    }

    public function testTryToMarkAsCompletedTodoItemWithWrongData()
    {
        $loggedUser = Bootstrap::createLoggedUser();
        $todoId = Bootstrap::getRandomLoggedUserTodoId($loggedUser->id);
        $newTodo = [
            "name" => "n",
            "completed" => 'aaaa',
            "user_id" => 'asdasd'
        ];

        $response = self::$helper->apiTest('patch', '/todo/' . $todoId, true, $newTodo);

        $this->assertSame($response['code'], 400);
        $this->assertEquals($response['data']['name'][0], 'Name must have a length between 3 and 30');
        $this->assertEquals($response['data']['completed'][0], 'Completed must be a boolean value');
        $this->assertEquals($response['data']['user_id'][0], 'User_id must be numeric');
    }

    public function testTryToDeleteTodoItemBelongingToLoggedUser()
    {
        $loggedUser = Bootstrap::createLoggedUser();
        $todoId = Bootstrap::getRandomLoggedUserTodoId($loggedUser->id);

        $response = self::$helper->apiTest('delete', '/todo/' . $todoId, true);

        $this->assertSame($response['code'], 200);
        $this->assertSame($response['data']['message'], 'Item has been deleted');
    }

    public function testTryToDeleteTodoItemBelongingToAnotherUser()
    {
        $usersId = Bootstrap::getUsersIdCollection();
        $todoId = Bootstrap::getRandomTodoId($usersId);
        Bootstrap::createLoggedUser();

        $response = self::$helper->apiTest('delete', '/todo/' . $todoId, true);

        $this->assertSame($response['code'], 403);
        $this->assertSame($response['data']['message'], 'Permission Denied');
    }

    public function testTryToDeleteTodoItemWhichDoesNotExist()
    {
        $id = 99999;

        $response = self::$helper->apiTest('delete', '/todo/' . $id, true);

        $this->assertSame($response['code'], 404);
        $this->assertSame($response['data']['message'], 'Item not found');
    }
}
