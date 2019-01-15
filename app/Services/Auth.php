<?php

namespace App\Services;

use App\Models\User;
use Firebase\JWT\JWT;
use Tuupola\Base62;

class Auth
{
    /**
     * @throws \Exception
     */
    static public function getToken(User $user)
    {
        $now = new \DateTime();
        $future = new \DateTime("now +2 hours");
        $jti = (new Base62())->encode(\random_bytes(16));

        $payload = [
            "iat" => $now->getTimeStamp(),
            "exp" => $future->getTimeStamp(),
            "jti" => $jti,
            "loggedUserId" => $user->id,
        ];

        return $token = JWT::encode($payload, $_ENV['JWT_SECRET'], "HS256");
    }

    static public function checkPasswords($password, $hashedPassword)
    {
        return password_verify($password, $hashedPassword);
    }
}
