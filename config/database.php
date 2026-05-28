<?php

declare(strict_types=1);

use App\Config\EnvLoader;

EnvLoader::load(dirname(__DIR__) . '/.env');

return [
    'host'     => EnvLoader::require('DB_HOST'),
    'port'     => (int) EnvLoader::get('DB_PORT', '8000'),
    'dbname'   => EnvLoader::require('DB_NAME'),
    'username' => EnvLoader::require('DB_USER'),
    'password' => EnvLoader::get('DB_PASSWORD', ''),
    'charset'  => EnvLoader::get('DB_CHARSET', 'utf8mb4'),
];
