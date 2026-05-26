<?php

require_once __DIR__ . '/Annonce.php';

class AnnonceLocation extends Annonce
{
    protected float $loyer;
    protected float $charges;

    public function __construct(
        BienImmo $bien,
        float $loyer,
        float $charges = 0.0,
        ?DateTimeImmutable $datePublication = null,
    ) {
        parent::__construct($bien, $datePublication);
        $this->setLoyer($loyer);
        $this->setCharges($charges);
    }

    public function getLoyer(): float
    {
        return $this->loyer;
    }

    public function setLoyer(float $loyer): void
    {
        if ($loyer <= 0) {
            throw new InvalidArgumentException('Le loyer doit être strictement positif.');
        }
        $this->loyer = $loyer;
    }

    public function getCharges(): float
    {
        return $this->charges;
    }

    public function setCharges(float $charges): void
    {
        if ($charges < 0) {
            throw new InvalidArgumentException('Les charges ne peuvent pas être négatives.');
        }
        $this->charges = $charges;
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
