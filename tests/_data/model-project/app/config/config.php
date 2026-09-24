<?php

declare(strict_types=1);

use Phalcon\Config\Config;

return new Config([
    'database' => [
        'adapter'  => 'Mysql',
        'host'     => 'mysql',
        'port'     => 3306,
        'username' => 'devtools',
        'password' => 'password',
        'dbname'   => 'devtools',
        'charset'  => 'utf8mb4',
    ],

    'application' => [
        'modelsDir' => __DIR__ . '/../models/',
    ],
]);