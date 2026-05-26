<?php

declare(strict_types=1);

require_once __DIR__ . '/BienImmo.php';

class Maison extends BienImmo
{
    protected float $terrain;

    public function __construct(string $ville, int|float $surface, int $chambres, int|float $terrain, ?string $description = null)
    {
        parent::__construct($ville, $surface, $chambres, $description);
        $this->setTerrain($terrain);
    }

    public function getTerrain(): float
    {
        return $this->terrain;
    }

    public function setTerrain(int|float $terrain): void
    {
        if ($terrain <= 0) {
            throw new InvalidArgumentException('La surface du terrain ne peut pas être négative ou égale à zéro.');
        }
        $this->terrain = (float) $terrain;
    }

    public function getType(): string
    {
        return 'Maison';
    }
}
