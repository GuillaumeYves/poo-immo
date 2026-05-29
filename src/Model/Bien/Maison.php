<?php

declare(strict_types=1);

namespace App\Model\Bien;

use InvalidArgumentException;
use RuntimeException;

/* *
 * Représente une maison, qui est un type de bien immobilier, avec des
 * informations spécifiques telles que la surface du terrain, en plus des
 * informations générales d'un bien.
 */
class Maison extends Bien
{
    protected readonly float $terrain;

    public function __construct(
        string $id,
        string $ville,
        int|float $surface,
        int $chambres,
        int|float $terrain,
        ?TypeBien $type = null,
    ) {
        parent::__construct($id, $ville, $surface, $chambres, $type);

        if ($terrain <= 0) {
            throw new InvalidArgumentException('La surface du terrain ne peut pas être négative ou égale à zéro.');
        }
        $this->terrain = (float) $terrain;
    }

    public static function fromArray(array $row): static
    {
        return new static(
            (string) ($row['bien_id'] ?? throw new RuntimeException('Bien sans id.')),
            (string) ($row['ville']   ?? throw new RuntimeException('Bien sans ville.')),
            (float) ($row['surface']  ?? throw new RuntimeException('Bien sans surface.')),
            (int) ($row['chambres']   ?? throw new RuntimeException('Bien sans chambres.')),
            (float) ($row['terrain']  ?? throw new RuntimeException('Maison sans terrain.')),
            self::typeFromRow($row),
        );
    }

    public function getTerrain(): float
    {
        return $this->terrain;
    }

    public function getCategorie(): CategorieBien
    {
        return CategorieBien::Maison;
    }

    public function getAttributsSpecifiques(): array
    {
        return [
            ['Terrain', sprintf('%.0f m²', $this->terrain)],
        ];
    }
}
