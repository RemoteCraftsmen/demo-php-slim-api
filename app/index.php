<?php
/**
 * Created by PhpStorm.
 * User: kamil
 * Date: 2018-12-07
 * Time: 11:52
 */

use Slim\Http\Request;

require_once __DIR__ . '/../vendor/autoload.php';

//gives us access to $_ENV and getenv()
try {
    (new Dotenv\Dotenv(__DIR__ . '/../'))->load();
} catch (Dotenv\Exception\InvalidPathException $e) {

}

//create instance of Slim Framework with settings
// settings will be saved in conatiner -> $container['settings']
$app = new Slim\App([
    'settings' => [
        'displayErrorDetails' => getenv('APP_DEBUG') === 'true',
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
        ]
    ],
]);

//Set our view engine, we are using Twig
//Directory to all twig templates should be -> resources/views
$container = $app->getContainer();

//Set up Twig templates
$container['view'] = function ($container) {
    $view = new \Slim\Views\Twig(__DIR__ . '/../resources/views', [
        'cache' => $container->settings['views']['cache']
    ]);
    $basePath = rtrim(str_ireplace('index.php', '', $container['request']->getUri()->getBasePath()), '/');
    $view->addExtension(new Slim\Views\TwigExtension($container['router'], $basePath));
    return $view;
};

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

//MIDDLEWARES
$app->add(new Tuupola\Middleware\JwtAuthentication([
    "path" => ["/"],
    "ignore" => ["/users", "/auth/login"],
    "secret" => getenv("JWT_SECRET"),
    "relaxed" => ["localhost", "slim.test"],
    "error" => function ($response, $arguments) {
        $data = [];
        $data["status"] = "error";
        $data["message"] = $arguments["message"];
        return $response->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    }

]));


require_once __DIR__ . '/../routes/web.php';

$app->run();
