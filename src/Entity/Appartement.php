<?php

declare(strict_types=1);

namespace App\Entity;

use InvalidArgumentException;

class Appartement extends BienImmo
{
    protected readonly int $etage;

    public function __construct(string $ville, int|float $surface, int $chambres, int $etage = 0, ?string $description = null)
    {
        parent::__construct($ville, $surface, $chambres, $description);

        if ($etage < 0) {
            throw new InvalidArgumentException("L'étage ne peut pas être négatif.");
        }
        $this->etage = $etage;
    }

    public function getEtage(): int
    {
        return $this->etage;
    }

    public function getType(): string
    {
        return 'Appartement';
    }
}
