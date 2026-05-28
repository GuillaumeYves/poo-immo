<?php

declare(strict_types=1);

namespace App\Exporter;

use App\Entity\Annonce\Annonce;

interface ExporterInterface
{
    /**
     * @param Annonce[] $annonces
     */
    public function export(array $annonces): string;

    public function getContentType(): string;

    public function getFilename(): string;

    public function getFormat(): string;
}
