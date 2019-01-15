<?php

namespace App\Tests\Factories;

use App\Models\Todo;
use Faker\Factory;

class ToDoFactory
{
    public static function create($object = [])
    {
        $faker = Factory::create();
        $objectToCreate = array_merge(
            [
                'name' => $faker->text(20),
                'user_id' => $faker->randomDigit,
                'creator_id' => $faker->randomDigit,
            ],
            $object
        );

        return Todo::create($objectToCreate);
    }
}
