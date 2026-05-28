<?php

declare(strict_types=1);

namespace App\Exporter;

use App\Entity\Annonce\Annonce;

final class AnnonceArrayConverter
{
    public const COLUMNS = [
        'type_bien',
        'ville',
        'surface_m2',
        'chambres',
        'etage',
        'terrain_m2',
        'transaction',
        'etat',
        'date_publication',
        'prix',
        'loyer',
        'charges',
    ];

    /**
     * @return array<string, string|int|float|null>
     */
    public static function toArray(Annonce $annonce): array
    {
        $bien = $annonce->getBien();

        $base = [
            'type_bien'        => $bien->getType(),
            'ville'            => $bien->getVille(),
            'surface_m2'       => $bien->getSurface(),
            'chambres'         => $bien->getChambres(),
            'transaction'      => $annonce->getTypeTransaction(),
            'etat'             => $annonce->getEtat()->value,
            'date_publication' => $annonce->getDatePublication()->format('Y-m-d'),
        ];

        return array_merge(
            array_fill_keys(self::COLUMNS, null),
            $base,
            $bien->toExportRow(),
            $annonce->toExportRow(),
        );
    }
}
