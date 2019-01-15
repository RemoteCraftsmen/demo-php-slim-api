<?php

namespace App\Tests\Factories;

use App\Models\User;
use Faker\Factory;

class UserFactory
{
    public static function create($user = []): User
    {
        $faker = Factory::create();
        $userToCreate = array_merge(
            [
                'email' => $faker->unique()->email,
                'password' => $faker->password,
                'first_name' => $faker->firstName,
                'last_name' => $faker->lastName,
                'username' => $faker->unique()->userName
            ],
            $user
        );

        $userToCreate['password'] = password_hash($userToCreate['password'], PASSWORD_BCRYPT);

        return User::create($userToCreate);
    }
}
