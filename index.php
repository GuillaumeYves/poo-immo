<?php

declare(strict_types=1);

require_once __DIR__ . '/src/autoload.php';

use App\Controller\AnnonceController;
use App\Controller\BienController;
use App\Controller\ExportController;
use App\Http\Request;
use App\Model\Annonce\AnnonceFactory;
use App\Model\Bien\BienFactory;
use App\Model\Exporter\BienCsvExporter;
use App\Model\Exporter\BienJsonExporter;
use App\Model\Exporter\CsvExporter;
use App\Model\Exporter\JsonExporter;
use App\Model\Form\BienFormValidator;
use App\Model\Form\AnnonceFormMapper;
use App\Model\Form\AnnonceFormValidator;
use App\Model\Form\DecimalParser;
use App\Model\Form\ExportFilterExtractor;
use App\Model\Formatter\MoneyFormatter;
use App\Model\Logger\EtatLogger;
use App\Model\Logger\FileLogger;
use App\Model\Logger\LoyerLogger;
use App\Model\Logger\PrixLogger;
use App\Model\Repository\Database;
use App\Model\Repository\PdoAnnonceRepository;
use App\Model\Repository\PdoBienRepository;
use App\Model\Service\EtatService;
use App\Model\Service\LoyerService;
use App\Model\Service\PrixService;
use App\View\AnnoncePresenter;
use App\View\BienPresenter;
use App\View\CataloguePresenter;
use App\View\ViewRenderer;

/*
 * Point d'entrée de l'application.
 * Il initialise les composants nécessaires, 
 * tels que les repositories, les services, les validateurs, et les contrôleurs.
 * Ensuite, il traite la requête HTTP entrante et délègue le travail au contrôleur approprié, 
 * en fonction des paramètres de la requête.
 */

$database         = Database::getInstance();
$repository       = new PdoAnnonceRepository($database, new AnnonceFactory());
$bienRepository   = new PdoBienRepository($database, new BienFactory());
$annoncePresenter = new AnnoncePresenter(new MoneyFormatter());
$views            = new ViewRenderer(__DIR__ . '/views');

$prixService = new PrixService($repository);
$prixService->subscribe(new PrixLogger(new FileLogger(__DIR__ . '/logs/prix.log')));

$loyerService = new LoyerService();
$loyerService->subscribe(new LoyerLogger(new FileLogger(__DIR__ . '/logs/loyer.log')));

$etatService = new EtatService();
$etatService->subscribe(new EtatLogger(new FileLogger(__DIR__ . '/logs/etat.log')));

$decimals  = new DecimalParser();
$bienValidator = new BienFormValidator($decimals);
$validator = new AnnonceFormValidator($decimals, $bienValidator);
$mapper    = new AnnonceFormMapper($decimals, $validator);
$filters   = new ExportFilterExtractor($decimals);

$annonceController = new AnnonceController(
    $repository,
    $bienRepository,
    $annoncePresenter,
    new CataloguePresenter($annoncePresenter),
    $views,
    $prixService,
    $loyerService,
    $etatService,
    $validator,
    $mapper,
);

$bienController = new BienController(
    $bienRepository,
    new BienPresenter(),
    $views,
    $bienValidator,
);

$exportController = new ExportController(
    $repository,
    $bienRepository,
    $views,
    [
        'json' => new JsonExporter(),
        'csv'  => new CsvExporter(),
    ],
    [
        'json' => new BienJsonExporter(),
        'csv'  => new BienCsvExporter(),
    ],
    $filters,
    $decimals,
);

$request = Request::fromGlobals();
$action = $request->query('action', 'index') ?? 'index';
$controller = match (true) {
    $request->query('export') !== null || $action === 'export' => $exportController,
    in_array($action, ['biens', 'bien_create', 'bien_store'], true) => $bienController,
    default => $annonceController,
};

$controller->dispatch($request)->send();
