<?php

namespace App\Tests;

use App\Models\User;
use App\Tests\Factories\UserFactory;
use App\Tests\Setup\Bootstrap;
use App\Tests\Setup\Helper;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    /** @var Helper */
    static private $helper;

    public static function setUpBeforeClass()
    {
        self::$helper = Bootstrap::getApp();
        Bootstrap::clearDatabase();
    }

    protected function setUp()
    {
        Bootstrap::clearDatabase();
    }

    public function testTryToShowAllUsersDataToUserWhoLoggedIn()
    {
        Bootstrap::createLoggedUser();

        $response = self::$helper->apiRequest('get', '/users/', true);

        $this->assertSame($response['code'], 200);
    }

    public function testTryToShowAllUsersToUserWhoDoesNotHaveLoggedToken()
    {
        Bootstrap::createLoggedUser();

        $response = self::$helper->apiRequest('get', '/users/', false);

        $this->assertSame($response['code'], 401);
        $this->assertContains("Token not found", $response['data']['message']);
        $this->assertEquals($response['data']['status'], "error");
    }

    public function testTryToShowUsersDataOfLoggedUser()
    {
        $loggedUser = Bootstrap::createLoggedUser();
        $response = self::$helper->apiRequest('get', '/users/' . $loggedUser['id'], true);

        $this->assertSame($response['code'], 200);
        $this->assertFalse(empty($response['data']['user']));
    }

    public function testTryToShowAnotherUserDataToLoggedUser()
    {
        Bootstrap::createLoggedUser();
        $randomUser = UserFactory::create();
        $randomUserId = $randomUser['id'];

        $response = self::$helper->apiRequest('get', '/users/' . $randomUserId, true);

        $this->assertSame($response['code'], 403);
    }

    public function testTryToShowNotExistingUserDataToLoggedUser()
    {
        Bootstrap::createLoggedUser();
        $id = 99999;

        $response = self::$helper->apiRequest('get', '/users/' . $id, true);

        $this->assertSame($response['code'], 404);
    }

    public function testTryToUpdateLoggedUserData()
    {
        $dataToUpdate = [
            'email' => 'new@new.com',
            'username' => 'newusername'
        ];
        $loggedUser = Bootstrap::createLoggedUser();

        $response = self::$helper->apiRequest('put', '/users/' . $loggedUser['id'], true, $dataToUpdate);

        $this->assertSame($response['code'], 200);
        $this->assertEquals($response['data']['user']['email'], $dataToUpdate['email']);
        $this->assertEquals($response['data']['user']['username'], $dataToUpdate['username']);
        $this->assertEquals($response['data']['user']['first_name'], $loggedUser['first_name']);
        $this->assertEquals($response['data']['user']['last_name'], $loggedUser['last_name']);
        $this->assertEquals($response['data']['user']['id'], $loggedUser['id']);
    }

    public function testTryToUpdateAnotherUserData()
    {
        $dataToUpdate = [
            'email' => 'new@new.com',
            'username' => 'newusername'
        ];
        $randomUser = UserFactory::create();
        $randomUserId = $randomUser['id'];
        Bootstrap::createLoggedUser();

        $response = self::$helper->apiRequest('put', '/users/' . $randomUserId, true, $dataToUpdate);

        $this->assertSame($response['code'], 403);
        $this->assertEquals($response['data']['status'], "error");
    }

    public function testTryToUpdateNotExistingUserData()
    {
        $dataToUpdate = [
            'email' => 'new@new.com',
            'username' => 'newusername'
        ];
        $id = 999999999;

        $response = self::$helper->apiRequest('put', '/users/' . $id, true, $dataToUpdate);

        $this->assertSame($response['code'], 404);
        $this->assertEquals($response['data']['status'], "error");
    }

    public function testTryToUpdateLoggedUserWithWrongData()
    {
        $dataToUpdate = [
            'email' => 'new.com',
            'username' => 'n'
        ];
        $loggedUser = Bootstrap::createLoggedUser();

        $response = self::$helper->apiRequest('put', '/users/' . $loggedUser['id'], true, $dataToUpdate);

        $this->assertSame($response['code'], 400);
        $this->assertEquals($response['data']['email'][0], 'Email must be valid email');
    }

    public function testTryToDeleteAnotherUser()
    {
        Bootstrap::createLoggedUser();
        $randomUser = UserFactory::create();
        $randomUserId = $randomUser['id'];

        $response = self::$helper->apiRequest('delete', '/users/' . $randomUserId, true);
        $user = User::find($randomUserId);

        $this->assertTrue(isset($user));
        $this->assertEquals($response['data']['message'], 'Permission Denied');
    }

    public function testTryToDeleteNotExistingUser()
    {
        $id = 999999999;

        $response = self::$helper->apiRequest('delete', '/users/' . $id, true);
        $user = User::find($id);

        $this->assertFalse(isset($user));
        $this->assertEquals($response['data']['message'], 'User does not exist');
    }

    public function testTryToDeleteLoggedUser()
    {
        $loggedUser = Bootstrap::createLoggedUser();

        $url = sprintf('/users/%s', $loggedUser['id']);
        $response = self::$helper->apiRequest('delete', $url, true);
        $user = User::find($loggedUser['id']);

        $this->assertFalse(isset($user));
        $this->assertEquals($response['data']['message'], 'User has been deleted');
    }
}
