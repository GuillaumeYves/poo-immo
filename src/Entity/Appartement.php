<?php

declare(strict_types=1);

require_once __DIR__ . '/BienImmo.php';

class Appartement extends BienImmo
{
    protected int $etage;

    public function __construct(string $ville, int|float $surface, int $chambres, int $etage = 0, ?string $description = null)
    {
        parent::__construct($ville, $surface, $chambres, $description);
        $this->setEtage($etage);
    }

    public function getEtage(): int
    {
        return $this->etage;
    }

    public function setEtage(int $etage): void
    {
        if ($etage < 0) {
            throw new InvalidArgumentException("L'étage ne peut pas être négatif.");
        }
        $this->etage = $etage;
    }

    public function getType(): string
    {
        return 'Appartement';
    }
}
