<?php

declare(strict_types=1);

namespace App\Model\Exporter;

use App\Model\Bien\Bien;

/* *
 * Exportateur pour les biens immobiliers au format JSON, permettant de convertir
 * une liste de biens en une chaîne JSON formatée, avec des options pour l'encodage
 * UTF-8. Cette classe utilise un convertisseur pour transformer chaque bien en un
 * tableau associatif avant de les encoder en JSON, et gère les différentes
 * propriétés des biens en fonction de leur type.
 */
final class BienJsonExporter implements Exporter
{
    public function export(array $biens): string
    {
        $data = array_map(
            fn(Bien $bien): array => BienArrayConverter::toArray($bien),
            $biens,
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
        return 'biens-sans-annonce-' . date('Y-m-d') . '.json';
    }
}
