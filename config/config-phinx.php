<?php

$dotenv = Dotenv\Dotenv::create(dirname(__DIR__));
$dotenv->load();

return [
    'paths' => [
        'migrations' => __DIR__ . '/../app/DB/Migrations/src'
    ],
    'migration_base_class' => \App\DB\Migrations\Migration::class,
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_database' => 'dev',
        'dev' => [
            'adapter' => getenv('DB_CONNECTION'),
            'host' => getenv('DB_HOST'),
            'name' => getenv('DB_DATABASE'),
            'user' => getenv('DB_USERNAME'),
            'pass' => getenv('DB_PASSWORD'),
            'port' => getenv('DB_PORT')
        ]
    ]
];
