<?php

namespace App\Tests\Factories;

use Faker\Factory;
use App\Models\User;

class UserFactory
{
    public function generateRandomUsers()
    {
        $usersCollection = [];

        $faker = Factory::create();
        for ($i = 1; $i < 5; $i++) {
            $randomUser = [
                'id' => $i,
                'email' => $faker->email,
                'password' => $faker->password,
                'first_name' => $faker->firstName,
                'last_name' => $faker->lastName,
                'username' => $faker->userName
            ];
            array_push($usersCollection, User::create($randomUser)->toArray());
        }

        return $usersCollection;
    }
}
