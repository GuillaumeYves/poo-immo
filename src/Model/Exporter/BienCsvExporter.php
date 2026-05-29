<?php

declare(strict_types=1);

namespace App\Model\Exporter;

use RuntimeException;

/* *
 * Exportateur pour les biens immobiliers au format CSV, permettant de convertir
 * une liste de biens en une chaîne CSV, avec des options pour le séparateur
 * et l'inclusion d'un BOM pour l'encodage UTF-8. Cette classe utilise un flux
 * temporaire pour construire le CSV et gère les différentes propriétés des
 * biens en fonction de leur type.
 */
final class BienCsvExporter implements Exporter
{
    public function __construct(
        private readonly string $separator = ';',
        private readonly bool $withBom = true,
    ) {
    }

    public function export(array $biens): string
    {
        $handle = fopen('php://temp', 'r+');
        if ($handle === false) {
            throw new RuntimeException("Impossible d'ouvrir le flux temporaire CSV.");
        }

        fputcsv($handle, BienArrayConverter::COLUMNS, $this->separator, escape: '');

        foreach ($biens as $bien) {
            $row = BienArrayConverter::toArray($bien);
            fputcsv($handle, array_map(
                fn(string|int|float|null $v): string => $v === null ? '' : (string) $v,
                $row,
            ), $this->separator, escape: '');
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        if ($csv === false) {
            return '';
        }

        return $this->withBom ? "\xEF\xBB\xBF" . $csv : $csv;
    }

    public function getContentType(): string
    {
        return 'text/csv; charset=utf-8';
    }

    public function getFilename(): string
    {
        return 'biens-sans-annonce-' . date('Y-m-d') . '.csv';
    }
}
