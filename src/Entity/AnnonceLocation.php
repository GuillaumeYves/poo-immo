<?php

declare(strict_types=1);

namespace App\Entity;

use DateTimeImmutable;
use InvalidArgumentException;

class AnnonceLocation extends Annonce
{
    protected readonly float $loyer;
    protected readonly float $charges;

    public function __construct(
        BienImmo $bien,
        int|float $loyer,
        int|float $charges = 0.0,
        ?DateTimeImmutable $datePublication = null,
        EtatAnnonce $etat = EtatAnnonce::Disponible,
    ) {
        parent::__construct($bien, $datePublication, $etat);

        if ($loyer <= 0) {
            throw new InvalidArgumentException('Le loyer doit être strictement positif.');
        }
        if ($charges < 0) {
            throw new InvalidArgumentException('Les charges ne peuvent pas être négatives.');
        }

        $this->loyer   = (float) $loyer;
        $this->charges = (float) $charges;
    }

    public function getLoyer(): float
    {
        return $this->loyer;
    }

    public function getCharges(): float
    {
        return $this->charges;
    }

    public function getLoyerCharges(): float
    {
        return $this->loyer + $this->charges;
    }

    public function getTypeTransaction(): string
    {
        return 'Location';
    }

    public function getMontant(): float
    {
        return $this->loyer;
    }
}
