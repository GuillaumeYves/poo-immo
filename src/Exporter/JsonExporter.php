<?php

declare(strict_types=1);

namespace App\Exporter;

use App\Entity\Annonce\Annonce;

class JsonExporter implements ExporterInterface
{
    public function export(array $annonces): string
    {
        $data = array_map(
            fn(Annonce $a) => AnnonceArrayConverter::toArray($a),
            $annonces
        );

        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return $json === false ? '[]' : $json;
    }

    public function getContentType(): string
    {
        return 'application/json; charset=utf-8';
    }

    public function getFilename(): string
    {
        return 'catalogue-' . date('Y-m-d') . '.json';
    }

    public function getFormat(): string
    {
        return 'json';
    }
}
