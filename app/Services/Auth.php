<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Tuupola\Base62;
use \Datetime;

class Auth
{

    static function getToken($user)
    {

        try {
            $now = new DateTime();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        $future = new DateTime("now +2 hours");
        $jti = (new Base62())->encode(random_bytes(16));

        $payload = [
            "iat" => $now->getTimeStamp(),
            "exp" => $future->getTimeStamp(),
            "jti" => $jti,
            "logged_user" => $user->id,
        ];

        return $token = JWT::encode($payload, $_ENV['JWT_SECRET'], "HS256");
    }

    static function checkPasswords($password, $hashedPassword)
    {
        return password_verify($password, $hashedPassword);
    }
}