<?php

declare(strict_types=1);

namespace App\Model\Bien;

/* *
 * Enumération représentant les différentes catégories de biens immobiliers, avec
 * des méthodes pour obtenir un libellé lisible de chaque catégorie.
 */
enum CategorieBien: string
{
    case Appartement = 'appartement';
    case Maison      = 'maison';
    case Villa       = 'villa';

    public function libelle(): string
    {
        return match ($this) {
            self::Appartement => 'Appartement',
            self::Maison      => 'Maison',
            self::Villa       => 'Villa',
        };
    }
}
