<?php

namespace App\Tests;

use App\Models\Todo;
use App\Tests\Factories\UserFactory;
use App\Tests\Factories\ToDoFactory;
use App\Tests\Setup\Bootstrap;
use App\Tests\Setup\Helper;
use PHPUnit\Framework\TestCase;

class ToDoTest extends TestCase
{
    /** @var Helper */
    static private $helper;

    public static function setUpBeforeClass()
    {
        self::$helper = Bootstrap::getApp();
        Bootstrap::clearDatabase();
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

        $response = self::$helper->apiRequest('post', '/todo/', true, $newTodo);
        $todo = Todo::find($response['data']['id']);

        $this->assertSame($response['code'], 201);
        $this->assertEquals($response['data']['name'], $newTodo['name']);
        $this->assertEquals($response['data']['user_id'], $loggedUser['id']);
        $this->assertTrue(isset($todo));
    }

    public function testTryToCreateTodoItemWithTooLongName()
    {
        $newTodo = [
            'name' => str_repeat("a", 50)
        ];

        $response = self::$helper->apiRequest('post', '/todo/', true, $newTodo);
        $todo = Todo::find($response['data']['id']);

        $this->assertSame($response['code'], 400);
        $this->assertNotEquals($response['data']['name'], $newTodo['name']);
        $this->assertEquals($response['data']['name'][0], 'Name must have a length between 3 and 30');
        $this->assertFalse(isset($todo));
    }

    public function testTryToShowAllTodoItemsBelongingToLoggedUser()
    {
        $loggedUser = Bootstrap::createLoggedUser();
        $numberOfTodos = intval(rand(1, 3));
        for ($i = 0; $i < $numberOfTodos; $i++) {
            ToDoFactory::create([
                'user_id' => $loggedUser['id'],
                'creator_id' => $loggedUser['id'],
            ]);
        }

        $response = self::$helper->apiRequest('get', '/todo/', true);
        $todos = Todo::where('user_id', $loggedUser['id'])->get();

        $this->assertSame($response['code'], 200);
        $this->assertSame(count($response['data']), count($todos));
    }

    public function testTryToShowSingleTodoItemBelongingToLoggedUser()
    {
        $loggedUser = Bootstrap::createLoggedUser();
        $randomTodoId = ToDoFactory::create([
            'user_id' => $loggedUser['id'],
            'creator_id' => $loggedUser['id'],
        ])['id'];

        $response = self::$helper->apiRequest('get', '/todo/' . $randomTodoId, true);

        $this->assertSame($response['code'], 200);
        $this->assertEquals($response['data']['user_id'], $loggedUser['id']);
        $this->assertEquals($response['data']['creator_id'], $loggedUser['id']);
    }

    public function testTryToShowSingleTodoItemNotBelongingToLoggedUser()
    {
        $loggedUser = Bootstrap::createLoggedUser();
        $anotherUser = UserFactory::create();

        ToDoFactory::create([
            'user_id' => $anotherUser['id'],
            'creator_id' => $anotherUser['id'],
        ]);

        $todo = Todo::where('user_id', '!=', $loggedUser['id'])->first();

        $response = self::$helper->apiRequest('get', '/todo/' . $todo->id, true);

        $this->assertSame($response['code'], 403);
        $this->assertEquals($response['data']['message'], 'Permission Denied');
    }

    public function testTryToShowSingleTodoItemWhichDoesNotExist()
    {
        Bootstrap::createLoggedUser();
        $id = 999999999;

        $response = self::$helper->apiRequest('get', '/todo/' . $id, true);

        $this->assertSame($response['code'], 404);
        $this->assertEquals($response['data']['message'], 'Item not found');
    }

    public function testTryToUpdateTodoItemBelongingToLoggedUser()
    {
        $loggedUser = Bootstrap::createLoggedUser();
        $todo = ToDoFactory::create([
            'user_id' => $loggedUser['id'],
            'creator_id' => $loggedUser['id'],
        ]);
        $todoId = $todo['id'];
        $newTodo = [
            "name" => "new ToDo",
            "user_id" => 999999999
        ];

        $response = self::$helper->apiRequest('put', '/todo/' . $todoId, true, $newTodo);

        $this->assertSame($response['code'], 200);
        $this->assertEquals($response['data']['name'], $newTodo['name']);
        $this->assertEquals($response['data']['id'], $todoId);
        $this->assertEquals($response['data']['creator_id'], $loggedUser['id']);
        $this->assertNotEquals($response['data']['user_id'], $loggedUser['id']);
    }

    public function testTryToUpdateTodoItemWithTooShortName()
    {
        $loggedUser = Bootstrap::createLoggedUser();
        $todo = ToDoFactory::create([
            'user_id' => $loggedUser['id'],
            'creator_id' => $loggedUser['id'],
        ]);
        $todoId = $todo['id'];
        $newTodo = [
            "name" => "n",
            "user_id" => 1
        ];

        $response = self::$helper->apiRequest('put', '/todo/' . $todoId, true, $newTodo);

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

        $response = self::$helper->apiRequest('put', '/todo/' . $id, true, $newTodo);
        $todo = Todo::where('name', $newTodo['name'])->first();

        $this->assertSame($response['code'], 200);
        $this->assertTrue(isset($todo));
    }

    public function testTryToUpdateTodoItemWhichDoesNotBelongsToLoggedUser()
    {
        Bootstrap::createLoggedUser();
        $anotherUser = UserFactory::create();
        $todo = ToDoFactory::create([
            'user_id' => $anotherUser['id'],
            'creator_id' => $anotherUser['id'],
        ]);
        $todoId = $todo['id'];
        $newTodo = [
            "name" => "new ToDo",
            "user_id" => 1
        ];

        $response = self::$helper->apiRequest('put', '/todo/' . $todoId, true, $newTodo);

        $this->assertSame($response['code'], 403);
        $this->assertEquals($response['data']['message'], 'Permission Denied');
    }

    public function testTryToMarkAsCompletedTodoItemBelongingToLoggedUser()
    {
        $loggedUser = Bootstrap::createLoggedUser();
        $todo = ToDoFactory::create([
            'user_id' => $loggedUser['id'],
            'creator_id' => $loggedUser['id'],
        ]);
        $todoId = $todo['id'];
        $newTodo = [
            "name" => "new ToDo",
            "user_id" => 1
        ];

        $response = self::$helper->apiRequest('patch', '/todo/' . $todoId, true, $newTodo);

        $this->assertSame($response['code'], 200);
        $this->assertEquals($response['data']['id'], $todoId);
        $this->assertEquals($response['data']['user_id'], $newTodo['user_id']);
        $this->assertEquals($response['data']['creator_id'], $loggedUser['id']);
        $this->assertEquals($response['data']['name'], $newTodo['name']);
    }

    public function testTryToMarkAsCompletedTodoItemBelongingToAnotherUser()
    {
        Bootstrap::createLoggedUser();
        $anotherUser = UserFactory::create();
        $todo = ToDoFactory::create([
            'user_id' => $anotherUser['id'],
            'creator_id' => $anotherUser['id'],
        ]);
        $todoId = $todo['id'];
        $newTodo = [
            "name" => "new ToDo",
            "user_id" => 1
        ];

        $response = self::$helper->apiRequest('patch', '/todo/' . $todoId, true, $newTodo);

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

        $response = self::$helper->apiRequest('patch', '/todo/' . $id, true, $newTodo);

        $this->assertSame($response['code'], 404);
        $this->assertEquals($response['data']['message'], 'Item not found');
    }

    public function testTryToMarkAsCompletedTodoItemWithWrongData()
    {
        $loggedUser = Bootstrap::createLoggedUser();
        $todo = ToDoFactory::create([
            'user_id' => $loggedUser['id'],
            'creator_id' => $loggedUser['id'],
        ]);
        $todoId = $todo['id'];
        $newTodo = [
            "name" => "n",
            "completed" => 'aaaa',
            "user_id" => 'asdasd'
        ];

        $response = self::$helper->apiRequest('patch', '/todo/' . $todoId, true, $newTodo);

        $this->assertSame($response['code'], 400);
        $this->assertEquals($response['data']['name'][0], 'Name must have a length between 3 and 30');
        $this->assertEquals($response['data']['completed'][0], 'Completed must be a boolean value');
        $this->assertEquals($response['data']['user_id'][0], 'User_id must be numeric');
    }

    public function testTryToDeleteTodoItemBelongingToLoggedUser()
    {
        $loggedUser = Bootstrap::createLoggedUser();
        $todo = ToDoFactory::create([
            'user_id' => $loggedUser['id'],
            'creator_id' => $loggedUser['id'],
        ]);
        $todoId = $todo['id'];

        $response = self::$helper->apiRequest('delete', '/todo/' . $todoId, true);

        $this->assertSame($response['code'], 200);
        $this->assertSame($response['data']['message'], 'Item has been deleted');
    }

    public function testTryToDeleteTodoItemBelongingToAnotherUser()
    {
        Bootstrap::createLoggedUser();
        $anotherUser = UserFactory::create();
        $todo = ToDoFactory::create([
            'user_id' => $anotherUser['id'],
            'creator_id' => $anotherUser['id'],
        ]);
        $todoId = $todo['id'];

        $response = self::$helper->apiRequest('delete', '/todo/' . $todoId, true);

        $this->assertSame($response['code'], 403);
        $this->assertSame($response['data']['message'], 'Permission Denied');
    }

    public function testTryToDeleteTodoItemWhichDoesNotExist()
    {
        $id = 99999;

        $response = self::$helper->apiRequest('delete', '/todo/' . $id, true);

        $this->assertSame($response['code'], 404);
        $this->assertSame($response['data']['message'], 'Item not found');
    }
}
