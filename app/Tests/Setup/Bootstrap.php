<?php

namespace App\Tests\Setup;

use App\App;
use App\Models\Todo;
use App\Models\User;
use App\Tests\Factories\ToDoFactory;
use App\Tests\Factories\UserFactory;

class Bootstrap
{
    private static $app;
    private static $helper;
    private static $token;
    private static $loggedUser = [
        'email' => 'phpunit@email.com',
        'password' => 'phpunit',
        'first_name' => 'phpunit',
        'last_name' => 'phpunit',
        'username' => 'phpunit',
    ];

    public static function init()
    {
        self::$app = (new App)->get();
        self::$helper = new Helper(self::$app);
    }

    public static function getApp()
    {
        return static::$helper;
    }

    public static function clearDatabase()
    {
        if ($users = User::all()) {
            foreach ($users as $user) {
                $user->forceDelete();
            }
        }

        if ($todos = Todo::all()) {
            foreach ($todos as $todo) {
                $todo->forceDelete();
            }
        }
    }

    public static function createLoggedUser($userToCreate = NULL)
    {
        if (is_null($userToCreate)) {
            $user = self::$loggedUser;
        } else {
            $user = $userToCreate;
        }

        $user['password'] = password_hash($user['password'], PASSWORD_BCRYPT);

        $newUser = User::create($user);
        static::$helper->setToken($newUser);

        return $newUser;
    }

    /*
     *  USERS ACTIONS
     */
    public static function createUserCollection()
    {
        return UserFactory::generateRandomUsers();
    }

    public static function getRandomExistingUserId()
    {
        $userCollection = self::createUserCollection();
        return $userCollection[array_rand($userCollection, 1)]['id'];
    }

    public static function getUsersIdCollection()
    {
        $userCollection = self::createUserCollection();
        return array_column($userCollection, 'id');
    }

    /*
     *  TODO ACTIONS
     */
    public static function createTodoCollection($userId)
    {
        return ToDoFactory::getTodoCollection($userId);
    }

    public static function createLoggedUserTodoCollection($userId)
    {
        return ToDoFactory::generateLoggedUserTodos($userId);
    }

    public static function getRandomTodos($usersIdCollection)
    {
        return ToDoFactory::generateRandomTodos($usersIdCollection);
    }

    public static function getRandomLoggedUserTodoId($userId)
    {
        $todosCollection = self::createLoggedUserTodoCollection($userId);
        return $todosCollection[array_rand($todosCollection, 1)]['id'];
    }

    public static function getRandomTodoId($usersIdCollection)
    {
        $todosCollection = self::getRandomTodos($usersIdCollection);
        return $todosCollection[array_rand($todosCollection, 1)]['id'];
    }
}

Bootstrap::init();
