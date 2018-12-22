<?php

namespace App\Tests;

use App\Tests\Setup\Bootstrap;
use PHPUnit\Framework\TestCase;

class AuthorizationTest extends TestCase
{
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

    public function testUsersRegistrationWithProperData()
    {
        $user = [
            'email' => "phpunit@email" . date('mdYHis') . ".com",
            'password' => 'phpunit',
            'first_name' => 'phpunit',
            'last_name' => 'phpunit',
            'username' => 'phpunit',
        ];

        $response = self::$helper->apiTest('post', '/auth/register', false, $user);

        $this->assertSame($response['code'], 200);
        $this->assertTrue(isset($response['data']['user']));
        $this->assertTrue(isset($response['data']['token']));
    }

    public function testUsersRegistrationWithEmailAlreadyExistingInDatabase()
    {
        $alreadyExistedUser = [
            'email' => 'phpunit@email.com',
            'password' => 'phpunit',
            'first_name' => 'phpunit',
            'last_name' => 'phpunit',
            'username' => 'phpunit',
        ];

        Bootstrap::createLoggedUser($alreadyExistedUser);
        $response = self::$helper->apiTest('post', '/auth/register', false, $alreadyExistedUser);

        $this->assertSame($response['code'], 409);
        $this->assertEquals($response['data']['message'], "The user with such email already exist");
        $this->assertEquals($response['data']['status'], "error");
    }

    public function testUsersRegistrationWithWrongEmailFormat()
    {
        $user = [
            'email' => "phpunitemailcom",
            'password' => 'phpunit',
            'first_name' => 'phpunit',
            'last_name' => 'phpunit',
            'username' => 'phpunit',
        ];

        $response = self::$helper->apiTest('post', '/auth/register', false, $user);

        $this->assertSame($response['code'], 400);
    }

    public function testUsersRegistrationwithoutAllRequiredFields()
    {
        $user = [
            'email' => "phpunitemailcom",
            'password' => '',
            'first_name' => 'phpunit',
            'last_name' => '',
            'username' => 'phpunit',
        ];

        $response = self::$helper->apiTest('post', '/auth/register', false, $user);

        $this->assertSame($response['code'], 400);
    }


    public function testUsersLoginWithProperData()
    {
        $user = [
            'email' => "phpunit@email" . date('mdYHis') . ".com",
            'password' => 'phpunit',
            'first_name' => 'phpunit',
            'last_name' => 'phpunit',
            'username' => 'phpunit',
        ];
        Bootstrap::createLoggedUser($user);

        $response = self::$helper->apiTest('post', '/auth/login', false, [
            'email' => $user['email'],
            'password' => $user['password']
        ]);

        $this->assertSame($response['code'], 200);
        $this->assertTrue(isset($response['data']['user']));
        $this->assertTrue(isset($response['data']['token']));
    }


    public function testUsersLoginWithWrongPassword()
    {
        $user = Bootstrap::createLoggedUser();

        $response = self::$helper->apiTest('post', '/auth/login', false, [
            'email' => $user->email,
            'password' => 11111111,
        ]);

        $this->assertSame($response['code'], 401);
        $this->assertFalse(($response['data']['auth']));
        $this->assertEquals($response['data']['status'], "error");
    }

    public function testUsersLoginWithWrongEmail()
    {
        $user = [
            'email' => "phpunit@email" . date('mdYHis') . ".com",
            'password' => 'phpunit',
            'first_name' => 'phpunit',
            'last_name' => 'phpunit',
            'username' => 'phpunit',
        ];

        Bootstrap::createLoggedUser($user);

        $response = self::$helper->apiTest('post', '/auth/login', false, [
            'email' => 'a@a.com',
            'password' => $user['password'],
        ]);

        $this->assertSame($response['code'], 404);
        $this->assertFalse(($response['data']['auth']));
        $this->assertEquals($response['data']['status'], "error");
        $this->assertEquals($response['data']['message'], "User does not exist");
    }

}
