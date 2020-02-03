<?php

return [
  'default' => 'global',
  'migrations' => 'migrations',
  'connections' => [
    'global' => [
      'driver' => 'mysql',
      'engine' => 'InnoDB',
      'host' => env('DB_HOST'),
      'database' => env('GLOBAL_DATABASE'),
      'username' => env('GLOBAL_USERNAME'),
      'password' => env('GLOBAL_PASSWORD'),
      'charset'   => 'utf8',
      'collation' => 'utf8_unicode_ci',
    ]
  ]
];