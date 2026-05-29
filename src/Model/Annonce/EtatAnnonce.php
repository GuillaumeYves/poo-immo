<?php

declare(strict_types=1);

namespace App\Model\Annonce;

/* *
 * Enumération représentant les différents états possibles d'une annonce immobilière.
 */
enum EtatAnnonce: string
{
    case Disponible = 'disponible';
    case EnNegociation = 'en_negociation';
    case Indisponible = 'indisponible';

    public function getLibelle(): string
    {
        return match ($this) {
            self::Disponible    => 'Disponible',
            self::EnNegociation => 'En cours de négociation',
            self::Indisponible  => 'Indisponible',
        };
    }
}
