<?php

declare(strict_types=1);

require_once __DIR__ . '/Annonce.php';

class AnnonceLocation extends Annonce
{
    protected float $loyer;
    protected float $charges;

    public function __construct(
        BienImmo $bien,
        int|float $loyer,
        int|float $charges = 0.0,
        ?DateTimeImmutable $datePublication = null,
        EtatAnnonce $etat = EtatAnnonce::Disponible,
    ) {
        parent::__construct($bien, $datePublication, $etat);
        $this->setLoyer($loyer);
        $this->setCharges($charges);
    }

    public function getLoyer(): float
    {
        return $this->loyer;
    }

    public function setLoyer(int|float $loyer): void
    {
        if ($loyer <= 0) {
            throw new InvalidArgumentException('Le loyer doit être strictement positif.');
        }
        $this->loyer = (float) $loyer;
    }

    public function getCharges(): float
    {
        return $this->charges;
    }

    public function setCharges(int|float $charges): void
    {
        if ($charges < 0) {
            throw new InvalidArgumentException('Les charges ne peuvent pas être négatives.');
        }
        $this->charges = (float) $charges;
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
