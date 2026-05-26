<?php

require_once __DIR__ . '/ExporterInterface.php';
require_once __DIR__ . '/AnnonceArrayConverter.php';

class CsvExporter implements ExporterInterface
{
    public function __construct(
        private string $separator = ';',
        private bool $withBom = true,
    ) {
    }

    public function export(array $annonces): string
    {
        $handle = fopen('php://temp', 'r+');

        fputcsv($handle, AnnonceArrayConverter::COLUMNS, $this->separator);

        foreach ($annonces as $annonce) {
            $row = AnnonceArrayConverter::toArray($annonce);
            fputcsv($handle, array_map(
                fn($v) => $v === null ? '' : $v,
                $row
            ), $this->separator);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

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

    public function getFormat(): string
    {
        return 'csv';
    }
}
