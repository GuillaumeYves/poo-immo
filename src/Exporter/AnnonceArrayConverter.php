<?php

declare(strict_types=1);

namespace App\Exporter;

use App\Entity\Annonce\Annonce;
use App\Entity\Annonce\AnnonceLocation;
use App\Entity\Annonce\AnnonceVente;
use App\Entity\Bien\Appartement;
use App\Entity\Bien\BienImmo;
use App\Entity\Bien\Maison;

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
        'prix_initial',
        'prix_avec_reduction',
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
            self::bienToArray($bien),
            self::annonceToArray($annonce),
        );
    }

    /**
     * @return array<string, int|float|string|null>
     */
    private static function bienToArray(BienImmo $bien): array
    {
        if ($bien instanceof Appartement) {
            return ['etage' => $bien->getEtage()];
        }

        if ($bien instanceof Maison) {
            return ['terrain_m2' => $bien->getTerrain()];
        }

        return [];
    }

    /**
     * @return array<string, int|float|string|null>
     */
    private static function annonceToArray(Annonce $annonce): array
    {
        if ($annonce instanceof AnnonceVente) {
            return [
                'prix_initial'        => $annonce->getPrixInitial(),
                'prix_avec_reduction' => $annonce->getPrixCourant(),
            ];
        }

        if ($annonce instanceof AnnonceLocation) {
            return [
                'loyer'   => $annonce->getLoyer(),
                'charges' => $annonce->getCharges(),
            ];
        }

        return [];
    }
}
