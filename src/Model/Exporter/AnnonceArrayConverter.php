<?php

declare(strict_types=1);

namespace App\Model\Exporter;

use App\Model\Annonce\Annonce;
use App\Model\Annonce\AnnonceLocation;
use App\Model\Annonce\AnnonceVente;
use App\Model\Bien\Appartement;
use App\Model\Bien\Bien;
use App\Model\Bien\Maison;

/* *
 * Convertisseur pour transformer une annonce immobilière en un tableau associatif
 * de données, avec des clés correspondant aux colonnes d'une base de données ou
 * d'un fichier CSV. Ce convertisseur gère les différentes propriétés d'une
 * annonce et de son bien associé, en fonction du type de bien et du type
 * d'annonce (vente ou location).
 */
final class AnnonceArrayConverter
{
    public const COLUMNS = [
        'categorie',
        'type',
        'ville',
        'surface_m2',
        'chambres',
        'etage',
        'terrain_m2',
        'transaction',
        'etat',
        'date_publication',
        'derniere_modification',
        'prix_initial',
        'prix_courant',
        'loyer',
        'charges',
    ];

    public static function toArray(Annonce $annonce): array
    {
        $bien = $annonce->getBien();

        $base = [
            'categorie'             => $bien->getCategorie()->value,
            'type'                  => $bien->getType()?->value,
            'ville'                 => $bien->getVille(),
            'surface_m2'            => $bien->getSurface(),
            'chambres'              => $bien->getChambres(),
            'transaction'           => $annonce->getTypeTransaction(),
            'etat'                  => $annonce->getEtat()->value,
            'date_publication'      => $annonce->getDatePublication()->format('Y-m-d'),
            'derniere_modification' => $annonce->getDerniereModification()->format('Y-m-d H:i:s'),
        ];

        return array_merge(
            array_fill_keys(self::COLUMNS, null),
            $base,
            self::bienToArray($bien),
            self::annonceToArray($annonce),
        );
    }

    private static function bienToArray(Bien $bien): array
    {
        if ($bien instanceof Appartement) {
            return ['etage' => $bien->getEtage()];
        }

        if ($bien instanceof Maison) {
            return ['terrain_m2' => $bien->getTerrain()];
        }

        return [];
    }

    private static function annonceToArray(Annonce $annonce): array
    {
        if ($annonce instanceof AnnonceVente) {
            return [
                'prix_initial' => $annonce->getPrixInitial(),
                'prix_courant' => $annonce->getPrixCourant(),
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
