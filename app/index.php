<?php

use Slim\Http\{Request, Response, StatusCode};


require_once __DIR__ . '/../vendor/autoload.php';

//gives us access to $_ENV and getenv()
try {
    (new Dotenv\Dotenv(__DIR__ . '/../'))->load();
} catch (Dotenv\Exception\InvalidPathException $e) {

}

// create instance of Slim Framework with settings
// settings will be saved in conatiner -> $container['settings']
$app = new Slim\App([
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
    ],
]);

$container = $app->getContainer();

// To use Eloquent globally we have to set it up outside Container
$capsule = new \Illuminate\Database\Capsule\Manager;
$capsule->addConnection($container['settings']['db']);
$capsule->setAsGlobal();
$capsule->bootEloquent();

$container['db'] = function ($container) use ($capsule) {
    return $capsule;
};

//Set Validator
$container['validator'] = function ($container) {
    return new App\Validation\Validator;
};

//----------------------- MIDDLEWARES -----------------------------
$app->add(new Tuupola\Middleware\JwtAuthentication([
    "path" => ["/"],
    "ignore" => ["/auth/register", "/auth/login"],
    "secret" => getenv("JWT_SECRET"),
    "relaxed" => ["localhost", "slim.test"],
    "error" => function ($response, $arguments) {
        $data = [];
        $data["status"] = "error";
        $data["message"] = $arguments["message"];
        return $response->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    }

]));

//----------------------- ERRORLOGGER -----------------------------
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
    return $logger;
};

//----------------------- ERRORHANDLERS -----------------------------
$container['errorHandler'] = function ($container) {
    return function ($request, $response, $error) use ($container) {
        if ($error instanceof Illuminate\Database\QueryException) {
            if(getenv('APP_ENV')=='development') {
                $obj = new App\Handlers\ErrorLogger($container['logger']);
                $obj($request, $response, $error);
            }

            return $response->withJson([
                'status' => 'error',
                'message' => (getenv('APP_ENV')=='development')?$error->getMessage():'Internal Server Error'],
                StatusCode::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    };
};


require_once __DIR__ . '/../routes/web.php';

$app->run();
