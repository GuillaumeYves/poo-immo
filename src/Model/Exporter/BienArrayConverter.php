<?php

declare(strict_types=1);

namespace App\Model\Exporter;

use App\Model\Bien\Appartement;
use App\Model\Bien\Bien;
use App\Model\Bien\Maison;

/* *
 * Convertisseur pour transformer un bien immobilier en un tableau associatif
 * de données, avec des clés correspondant aux colonnes d'une base de données ou
 * d'un fichier CSV. Ce convertisseur gère les différentes propriétés d'un
 * bien en fonction de son type (appartement, maison, villa), en incluant les
 * propriétés spécifiques à chaque type de bien.
 */
final class BienArrayConverter
{
    public const COLUMNS = [
        'id',
        'categorie',
        'type',
        'ville',
        'surface_m2',
        'chambres',
        'etage',
        'terrain_m2',
    ];

    public static function toArray(Bien $bien): array
    {
        $row = [
            'id'         => $bien->getId(),
            'categorie'  => $bien->getCategorie()->value,
            'type'       => $bien->getType()?->value,
            'ville'      => $bien->getVille(),
            'surface_m2' => $bien->getSurface(),
            'chambres'   => $bien->getChambres(),
            'etage'      => null,
            'terrain_m2' => null,
        ];

        if ($bien instanceof Appartement) {
            $row['etage'] = $bien->getEtage();
        }
        if ($bien instanceof Maison) {
            $row['terrain_m2'] = $bien->getTerrain();
        }

        return $row;
    }
}
