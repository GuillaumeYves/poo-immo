<?php

declare(strict_types=1);

namespace App\Model\Exporter;

use RuntimeException;

/* *
 * Exportateur pour les annonces immobilières au format CSV, permettant de convertir
 * une liste d'annonces en une chaîne CSV, avec des options pour le séparateur
 * et l'inclusion d'un BOM pour l'encodage UTF-8. Cette classe utilise un flux
 * temporaire pour construire le CSV et gère les différentes propriétés des
 * annonces et de leurs biens associés en fonction du type de bien et du type
 * d'annonce (vente ou location).
 */
class CsvExporter implements Exporter
{
    public function __construct(
        private readonly string $separator = ';',
        private readonly bool $withBom = true,
    ) {
    }

    public function export(array $annonces): string
    {
        $handle = fopen('php://temp', 'r+');
        if ($handle === false) {
            throw new RuntimeException("Impossible d'ouvrir le flux temporaire CSV.");
        }

        fputcsv($handle, AnnonceArrayConverter::COLUMNS, $this->separator, escape: '');

        foreach ($annonces as $annonce) {
            $row = AnnonceArrayConverter::toArray($annonce);
            fputcsv($handle, array_map(
                fn(string|int|float|null $v): string => $v === null ? '' : (string) $v,
                $row
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
        return 'catalogue-' . date('Y-m-d') . '.csv';
    }
}
