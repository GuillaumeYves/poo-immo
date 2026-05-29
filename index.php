<?php

declare(strict_types=1);

require_once __DIR__ . '/src/autoload.php';

use App\Http\Request;

/*
 * Point d'entree HTTP de l'application.
 * Il transforme les superglobales en Request et confie le traitement au routing.
 */
$routes = require __DIR__ . '/routes.php';
$routes(Request::fromGlobals())->send();
