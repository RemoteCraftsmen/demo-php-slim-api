<?php

try {
    $dotenv = Dotenv\Dotenv::create(dirname(dirname(__DIR__)));
    $dotenv->load();
} catch (Dotenv\Exception\InvalidPathException $e) {
}

return [
    'settings' => [
        'displayErrorDetails' => getenv('APP_DEBUG') === 'true',
        'debug' => getenv('APP_DEBUG') === 'true',
        'app' => [
            'name' => getenv('APP_NAME')
        ],
        'views' => [
            'cache' => getenv('VIEW_CACHE_DISABLED') === 'true' ? false : __DIR__ . '/../storage/views'
        ],
        'db' => [
            'driver' => getenv('DB_CONNECTION'),
            'host' => getenv('DB_HOST'),
            'database' => getenv('DB_DATABASE'),
            'username' => getenv('DB_USERNAME'),
            'password' => getenv('DB_PASSWORD'),
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
        ],
        'logger' => [
            'name' => 'slim-app',
            'path' => __DIR__ . '/../logs/app.log',
            'level' => \Monolog\Logger::DEBUG,
        ],
        'jwt' => [
            "path" => ["/"],
            "ignore" => ["/auth/register", "/auth/login"],
            "secret" => getenv("JWT_SECRET"),
            "relaxed" => ["localhost", getenv('APP_DOMAIN')],
            "error" => function (\Slim\Http\Response $response, $arguments) {
                $data = [];
                $data["status"] = "error";
                $data["message"] = $arguments["message"];
                return $response->write(json_encode($data, JSON_UNESCAPED_SLASHES));
            }
        ]
    ],
];
