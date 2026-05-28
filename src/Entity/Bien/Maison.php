<?php

declare(strict_types=1);

namespace App\Entity\Bien;

use InvalidArgumentException;
use RuntimeException;

class Maison extends BienImmo
{
    protected readonly float $terrain;

    public function __construct(string $ville, int|float $surface, int $chambres, int|float $terrain, ?string $description = null)
    {
        parent::__construct($ville, $surface, $chambres, $description);

        if ($terrain <= 0) {
            throw new InvalidArgumentException('La surface du terrain ne peut pas être négative ou égale à zéro.');
        }
        $this->terrain = (float) $terrain;
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function fromArray(array $row): static
    {
        return new self(
            (string) ($row['ville']    ?? throw new RuntimeException('Bien sans ville.')),
            (float) ($row['surface']   ?? throw new RuntimeException('Bien sans surface.')),
            (int) ($row['chambres']    ?? throw new RuntimeException('Bien sans chambres.')),
            (float) ($row['terrain']   ?? throw new RuntimeException('Maison sans terrain.')),
            isset($row['description']) ? (string) $row['description'] : null,
        );
    }

    public function getTerrain(): float
    {
        return $this->terrain;
    }

    public function getType(): string
    {
        return 'Maison';
    }

    public function getAttributsAffichage(): array
    {
        return [
            ['Terrain', sprintf('%.0f m²', $this->terrain)],
        ];
    }

    public function toExportRow(): array
    {
        return ['terrain_m2' => $this->terrain];
    }
}
