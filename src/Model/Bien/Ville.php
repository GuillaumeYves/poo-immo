<?php

declare(strict_types=1);

namespace App\Model\Bien;

/* *
 * Enumération représentant les différentes villes où les biens immobiliers peuvent
 * être situés, avec des méthodes pour obtenir un libellé lisible de chaque ville.
 */
enum Ville: string
{
    case Paris       = 'Paris';
    case Lyon        = 'Lyon';
    case Marseille   = 'Marseille';
    case Toulouse    = 'Toulouse';
    case Bordeaux    = 'Bordeaux';
    case Nantes      = 'Nantes';
    case Strasbourg  = 'Strasbourg';
    case Montpellier = 'Montpellier';
    case Lille       = 'Lille';
    case Rennes      = 'Rennes';
    case Nice        = 'Nice';
    case Grenoble    = 'Grenoble';
    case Dijon       = 'Dijon';
    case Angers      = 'Angers';
    case Reims       = 'Reims';

    public function libelle(): string
    {
        return $this->value;
    }
}
