<?php

declare(strict_types=1);

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

    public function estActive(): bool
    {
        return $this !== self::Indisponible;
    }
}
