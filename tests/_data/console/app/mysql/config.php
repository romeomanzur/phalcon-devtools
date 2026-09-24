<?php

use Phalcon\Config\Config;
use Phalcon\Logger\Enum;

return new Config([
    'database' => [
        'adapter'  => 'Mysql',
        'host'     => env('MYSQL_DB_HOST') ?: '127.0.0.1',
        'username' => env('MYSQL_DB_USERNAME') ?: 'root',
        'password' => env('MYSQL_DB_PASSWORD') ?: '',
        'dbname'   => env('MYSQL_DB_NAME') ?: 'devtools',
        'port'     => (int) (env('MYSQL_DB_PORT') ?: 3306),
    ],
    'logger' => [
        'path'     => tests_path('_output/logs/console/mysql/'),
        'format'   => '%date% [%type%] %message%',
        'date'     => 'D j H:i:s',
        'logLevel' => Enum::DEBUG,
        'filename' => 'tests.log',
    ],
    'application' => [
        'controllersDir' => app_path() . '/controllers/',
        'modelsDir'      => app_path() . '/models/',
        'viewsDir'       => app_path() . '/views/',
    ],
]);
