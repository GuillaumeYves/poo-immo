<?php

declare(strict_types=1);

namespace App\Exporter;

use App\Entity\Annonce;
use App\Entity\AnnonceLocation;
use App\Entity\AnnonceVente;
use App\Entity\Appartement;
use App\Entity\Maison;

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

        $row = [
            'type_bien'        => $bien->getType(),
            'ville'            => $bien->getVille(),
            'surface_m2'       => $bien->getSurface(),
            'chambres'         => $bien->getChambres(),
            'etage'            => null,
            'terrain_m2'       => null,
            'transaction'      => $annonce->getTypeTransaction(),
            'etat'             => $annonce->getEtat()->value,
            'date_publication' => $annonce->getDatePublication()->format('Y-m-d'),
            'prix'             => null,
            'loyer'            => null,
            'charges'          => null,
        ];

        if ($bien instanceof Appartement) {
            $row['etage'] = $bien->getEtage();
        } elseif ($bien instanceof Maison) {
            $row['terrain_m2'] = $bien->getTerrain();
        }

        if ($annonce instanceof AnnonceVente) {
            $row['prix'] = $annonce->getPrix();
        } elseif ($annonce instanceof AnnonceLocation) {
            $row['loyer']   = $annonce->getLoyer();
            $row['charges'] = $annonce->getCharges();
        }

        return $row;
    }
}
