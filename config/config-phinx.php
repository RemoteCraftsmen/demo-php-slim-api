<?php

return [
  'paths' => [
    'migrations' => __DIR__ . '/../app/DB/Migrations/src'
  ],
  'migration_base_class' => \App\DB\Migrations\Migration::class,
  'environments' => [
    'default_migration_table' => 'phinxlog',
    'default_database' => 'dev',
    'dev' => [
      'adapter' =>  $_ENV['DB_CONNECTION'],
      'host'    =>  $_ENV['DB_HOST'],
      'name'    =>  $_ENV['DB_DATABASE'],
      'user'    =>  $_ENV['DB_USERNAME'],
      'pass'    =>  $_ENV['DB_PASSWORD'],
      'port'    =>  $_ENV['DB_PORT']
    ]
  ]
];