<?php

declare(strict_types=1);

/* Utilisation du EnvLoader pour lire les variables d'environnement depuis le fichier .env */
use App\Config\EnvLoader;
EnvLoader::load(dirname(__DIR__) . '/.env');

/* Configuration de la base de données avec les variables d'environnement */
return [
    'host'     => EnvLoader::require('DB_HOST'),
    'port'     => (int) EnvLoader::get('DB_PORT', '3306'),
    'dbname'   => EnvLoader::require('DB_NAME'),
    'username' => EnvLoader::require('DB_USER'),
    'password' => EnvLoader::get('DB_PASSWORD', ''),
    'charset'  => EnvLoader::get('DB_CHARSET', 'utf8mb4'),
];
