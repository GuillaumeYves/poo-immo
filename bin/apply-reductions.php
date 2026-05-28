<?php

declare(strict_types=1);

/**
 * Applique des réductions à des annonces
 * Usage : php bin/apply-reductions.php
 */

require_once __DIR__ . '/../src/autoload.php';

use App\Database\AnnonceRepository;
use App\Logger\ReductionLogger;
use App\Service\AppliqueReduction;

$repository = new AnnonceRepository();
$service    = new AppliqueReduction($repository);
$service->subscribe(new ReductionLogger(__DIR__ . '/../logs/logs.txt'));

/** @var array<int, array{0: int, 1: string}> $reductions */
$reductions = [
    [1,  '15'],     // Lyon       - Appartement
    [7,  '5.5'],    // Strasbourg - Maison
    [10, '10'],     // Rennes     - Maison
    [13, '20'],     // Dijon      - Maison
];

foreach ($reductions as [$annonceId, $pourcentage]) {
    try {
        $service->appliquer($annonceId, $pourcentage);
        echo "✓ annonce id={$annonceId} : -{$pourcentage}%\n";
    } catch (Throwable $e) {
        fwrite(STDERR, "✗ annonce id={$annonceId} : {$e->getMessage()}\n");
    }
}

echo "\nLog : logs/logs.txt\n";
