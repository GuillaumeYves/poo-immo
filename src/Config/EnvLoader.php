<?php

declare(strict_types=1);

namespace App\Config;

use RuntimeException;

/* *
 * Charge les variables d'environnement à partir d'un fichier .env
 * et fournit des méthodes pour accéder à ces variables.
 */
final class EnvLoader
{
    public static function load(string $path): void
    {
        if (!is_file($path) || !is_readable($path)) {
            throw new RuntimeException("Fichier .env introuvable ou illisible : {$path}");
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            throw new RuntimeException("Impossible de lire le fichier .env : {$path}");
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            if (!str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key   = trim($key);
            $value = trim($value);

            if (strlen($value) >= 2) {
                $first = $value[0];
                $last  = $value[strlen($value) - 1];
                if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                    $value = substr($value, 1, -1);
                }
            }

            if (array_key_exists($key, $_ENV)) {
                continue;
            }
            $_ENV[$key]    = $value;
            $_SERVER[$key] = $value;
        }
    }

    public static function require(string $key): string
    {
        return $_ENV[$key]
            ?? throw new RuntimeException("Variable d'environnement requise manquante : {$key}");
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        return $_ENV[$key] ?? $default;
    }
}
