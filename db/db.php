<?php

declare(strict_types=1);

/**
 * Lance les commandes dans l'ordre pour démarrer la bdd
 * Usage : php db/db.php
 */

require_once __DIR__ . '/../src/autoload.php';

use App\Database\Database;

$sqlDir = __DIR__ . '/sql';
$files  = glob($sqlDir . '/*.sql');

if ($files === false || $files === []) {
    fwrite(STDERR, "Aucun fichier .sql trouvé dans {$sqlDir}\n");
    exit(1);
}

sort($files, SORT_NATURAL);

$db = Database::bootstrap();

foreach ($files as $file) {
    $name = basename($file);
    echo "→ {$name}\n";

    $sql = file_get_contents($file);
    if ($sql === false) {
        fwrite(STDERR, "Impossible de lire {$file}\n");
        exit(1);
    }

    try {
        $db->execScript($sql);
    } catch (Throwable $e) {
        fwrite(STDERR, "  ✗ {$e->getMessage()}\n");
        exit(1);
    }

    echo "  ✓ ok\n";
}

echo "\nBase prête.\n";
