<?php

require_once __DIR__ . '/BienImmo.php';

class Maison extends BienImmo
{
    protected float $terrain;

    public function __construct(string $ville, float $surface, int $chambres, float $terrain)
    {
        parent::__construct($ville, $surface, $chambres);
        $this->setTerrain($terrain);
    }

    public function getTerrain(): float
    {
        return $this->terrain;
    }

    public function setTerrain(float $terrain): void
    {
        if ($terrain <= 0) {
            throw new InvalidArgumentException('La surface du terrain ne peut pas être négative ou égale à zéro.');
        }
        $this->terrain = $terrain;
    }

    public function getType(): string
    {
        return 'Maison';
    }
}
