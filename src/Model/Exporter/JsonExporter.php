<?php

declare(strict_types=1);

namespace App\Model\Exporter;

use App\Model\Annonce\Annonce;

/* *
 * Exportateur pour les annonces immobilières au format JSON, permettant de convertir
 * une liste d'annonces en une chaîne JSON formatée, avec des options pour l'encodage
 * UTF-8. Cette classe utilise un convertisseur pour transformer chaque annonce en un
 * tableau associatif avant de les encoder en JSON, et gère les différentes
 * propriétés des annonces et de leurs biens associés en fonction du type de bien
 * et du type d'annonce (vente ou location).
 */
class JsonExporter implements Exporter
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
}
