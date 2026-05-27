<?php

declare(strict_types=1);

require_once __DIR__ . '/src/autoload.php';

use App\Database\JsonDataRepository;
use App\Exporter\CsvExporter;
use App\Exporter\ExporterInterface;
use App\Exporter\JsonExporter;
use App\Formatter\MoneyFormatter;
use App\Presenter\AnnoncePresenter;
use App\Presenter\CataloguePresenter;

$repository = new JsonDataRepository(__DIR__ . '/data');
$presenter  = new CataloguePresenter(new AnnoncePresenter(new MoneyFormatter()));

/** @var ExporterInterface[] $exporters */
$exporters = [];
foreach ([new JsonExporter(), new CsvExporter()] as $exporter) {
    $exporters[$exporter->getFormat()] = $exporter;
}

$exportFormat = $_GET['export'] ?? null;
if (is_string($exportFormat) && isset($exporters[$exportFormat])) {
    $exporter = $exporters[$exportFormat];
    $payload  = $exporter->export($repository->findAll());

    header('Content-Type: ' . $exporter->getContentType());
    header('Content-Disposition: attachment; filename="' . $exporter->getFilename() . '"');
    echo $payload;
    exit;
}

$catalogue = $presenter->presenter($repository->findAll());

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <script src="assets/js/recherche.js" defer></script>
</head>
<body>

<section class="catalogue">
    <h2><?= htmlspecialchars($catalogue['entete'], ENT_QUOTES, 'UTF-8') ?></h2>

    <nav class="exports">
        <?php foreach ($exporters as $format => $exporter): ?>
            <a class="export export--<?= htmlspecialchars($format, ENT_QUOTES, 'UTF-8') ?>"
               href="?export=<?= htmlspecialchars($format, ENT_QUOTES, 'UTF-8') ?>">
                <button type="button">Exporter en <?= strtoupper(htmlspecialchars($format, ENT_QUOTES, 'UTF-8')) ?></button>
            </a>
        <?php endforeach; ?>
    </nav>

    <form class="recherche" role="search" onsubmit="return false">
        <label for="recherche-input">Rechercher :</label>
        <input type="search"
               id="recherche-input"
               name="q"
               autocomplete="off"
               spellcheck="false"
               placeholder="Exemple : Lyon">
        <span class="recherche__compteur" id="recherche-compteur"></span>
    </form>

    <div class="catalogue__liste" id="catalogue-liste">
        <?php foreach ($catalogue['annonces'] as $i => $annonce): ?>
            <?php if ($i > 0): ?><hr><?php endif; ?>
            <article class="annonce annonce--<?= strtolower($annonce['transaction']) ?> annonce--etat-<?= htmlspecialchars($annonce['etat'], ENT_QUOTES, 'UTF-8') ?>"
                     data-titre="<?= htmlspecialchars($annonce['titre'], ENT_QUOTES, 'UTF-8') ?>">
                <h3><?= htmlspecialchars($annonce['titre'], ENT_QUOTES, 'UTF-8') ?></h3>
                <p class="annonce__meta">
                    <?php foreach ($annonce['meta'] as $j => $ligne): ?>
                        <?php if ($j > 0): ?><br><?php endif; ?>
                        <?= htmlspecialchars($ligne, ENT_QUOTES, 'UTF-8') ?>
                    <?php endforeach; ?>
                </p>
                <ul>
                    <?php foreach ($annonce['attributs'] as [$label, $valeur]): ?>
                        <li><strong><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?> :</strong> <?= htmlspecialchars($valeur, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
            </article>
        <?php endforeach; ?>
    </div>
</section>

</body>
</html>
