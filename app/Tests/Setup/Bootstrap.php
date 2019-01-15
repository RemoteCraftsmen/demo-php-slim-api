<?php

namespace App\Tests\Setup;

use App\App;
use App\Models\Todo;
use App\Models\User;
use App\Tests\Factories\UserFactory;

class Bootstrap
{
    /** @var Helper */
    private static $helper;

    private static $loggedUser = [
        'email' => 'phpunit@email.com',
        'password' => 'phpunit',
        'first_name' => 'phpunit',
        'last_name' => 'phpunit',
        'username' => 'phpunit',
    ];

    public static function init()
    {
        $app = (new App)->get();
        self::$helper = new Helper($app);
    }

    public static function getApp()
    {
        return static::$helper;
    }

    public static function clearDatabase()
    {
        User::query()->truncate();
        Todo::query()->truncate();
    }

    public static function createLoggedUser($userToCreate = [])
    {
        if (is_null($userToCreate)) {
            $user = self::$loggedUser;
        } else {
            $user = $userToCreate;
        }

        $newUser = UserFactory::create($user);

        static::$helper->setToken($newUser);

        return $newUser->toArray();
    }
}

Bootstrap::init();
