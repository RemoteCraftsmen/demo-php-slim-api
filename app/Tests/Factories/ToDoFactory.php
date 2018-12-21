<?php

namespace App\Tests\Factories;

use Faker\Factory;
use App\Models\Todo;

class ToDoFactory
{
    public static function generateRandomTodos($usersIdsCollection)
    {
        $randomTodoCollection = [];

        $faker = Factory::create();
        for ($i = 0; $i < count($usersIdsCollection); $i++) {
            $randomToDo = [
                'name' => $faker->text(20),
                'user_id' => $usersIdsCollection[$i],
                'creator_id' => $usersIdsCollection[$i],
            ];
            array_push($randomTodoCollection, Todo::create($randomToDo)->toArray());
        }

        return $randomTodoCollection;
    }

    public static function generateLoggedUserTodos($loggedUserId)
    {
        $loggedTodoCollection = [];

        $faker = Factory::create();
        for ($i = 1; $i < 4; $i++) {
            $randomToDo = [
                'name' => $faker->text(20),
                'user_id' => $loggedUserId,
                'creator_id' => $loggedUserId,
            ];
            array_push($loggedTodoCollection, Todo::create($randomToDo)->toArray());
        }

        return $loggedTodoCollection;
    }

    public static function getTodoCollection($loggedUserId)
    {
        return array_merge(self::generateRandomTodos(), self::generateLoggedUserTodos($loggedUserId));
    }
}
