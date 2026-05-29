<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/autoload.php';

/* Récupère la config de la base de données */
use App\Model\Repository\Database;

/* Prépare et exécute les scripts SQL pour créer la base de données et les tables */
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
    echo "{$name}\n";

    $sql = file_get_contents($file);
    if ($sql === false) {
        fwrite(STDERR, "Impossible de lire {$file}\n");
        exit(1);
    }

    try {
        $db->execScript($sql);
        echo "→ succès'\n";
    } catch (Throwable $e) {
        fwrite(STDERR, "  ✗ {$e->getMessage()}\n");
        echo "→ échec'\n";
        exit(1);
    }
}
