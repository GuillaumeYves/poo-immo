<?php

declare(strict_types=1);

require_once __DIR__ . '/../Entity/Annonce.php';

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
