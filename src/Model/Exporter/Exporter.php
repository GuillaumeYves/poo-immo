<?php

declare(strict_types=1);

namespace App\Model\Exporter;

/* *
 * Interface définissant les méthodes nécessaires pour exporter des données, telles
 * que des annonces ou des biens immobiliers, dans différents formats (CSV, JSON,
 * XML). Chaque exportateur doit implémenter cette interface pour garantir une
 * compatibilité avec le reste de l'application et permettre une utilisation
 * interchangeable des différents formats d'exportation.
 */
interface Exporter
{
    public function export(array $annonces): string;

    public function getContentType(): string;

    public function getFilename(): string;
}
