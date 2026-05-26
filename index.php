<?php

require_once __DIR__ . '/src/Entity/Appartement.php';
require_once __DIR__ . '/src/Entity/Maison.php';
require_once __DIR__ . '/src/Entity/AnnonceVente.php';
require_once __DIR__ . '/src/Entity/AnnonceLocation.php';
require_once __DIR__ . '/src/Repository/AnnonceRepository.php';
require_once __DIR__ . '/src/Presenter/BienPresenter.php';
require_once __DIR__ . '/src/Exporter/ExporterInterface.php';
require_once __DIR__ . '/src/Exporter/JsonExporter.php';
require_once __DIR__ . '/src/Exporter/CsvExporter.php';

$repository = new AnnonceRepository();
$presenter  = new BienPresenter();

$repository->add(new AnnonceVente(new Appartement('Lyon', 45, 2, 3), 180000));
$repository->add(new AnnonceVente(new Appartement('Paris', 30, 1, 5), 320000));
$repository->add(new AnnonceLocation(new Appartement('Marseille', 60, 3, 1), 850, 120));
$repository->add(new AnnonceVente(new Maison('Toulouse', 120, 4, 500), 380000));
$repository->add(new AnnonceLocation(new Maison('Bordeaux', 95, 3, 200), 1200, 150));

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
    header('Content-Length: ' . strlen($payload));
    echo $payload;
    exit;
}

$catalogue = $presenter->presenterCatalogue($repository->findAll());

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
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

    <?php foreach ($catalogue['annonces'] as $i => $annonce): ?>
        <?php if ($i > 0): ?><hr><?php endif; ?>
        <article class="annonce annonce--<?= strtolower($annonce['transaction']) ?>">
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
</section>

</body>
</html>
