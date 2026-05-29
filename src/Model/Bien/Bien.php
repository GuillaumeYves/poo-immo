<?php

declare(strict_types=1);

namespace App\Model\Bien;

use InvalidArgumentException;

/* *
 * Représente un bien immobilier générique, avec des informations de base telles que
 * l'id, la ville, la surface, le nombre de chambres, etc. Les classes spécifiques
 * de biens (appartement, maison, terrain) étendent cette classe pour ajouter des
 * informations spécifiques à chaque type de bien.
 */
abstract class Bien
{
    protected readonly string $id;
    protected readonly string $ville;
    protected readonly float $surface;
    protected readonly int $chambres;
    protected readonly ?TypeBien $type;

    public function __construct(
        string $id,
        string $ville,
        int|float $surface,
        int $chambres,
        ?TypeBien $type = null,
    ) {
        if (trim($id) === '') {
            throw new InvalidArgumentException("L'id du bien ne peut pas être vide.");
        }
        if (trim($ville) === '') {
            throw new InvalidArgumentException('La ville ne peut pas être vide.');
        }
        if ($surface <= 0) {
            throw new InvalidArgumentException('La surface ne peut pas être négative ou égale à zéro.');
        }
        if ($chambres < 0) {
            throw new InvalidArgumentException('Le nombre de chambres ne peut pas être négatif.');
        }

        $this->id       = $id;
        $this->ville    = $ville;
        $this->surface  = (float) $surface;
        $this->chambres = $chambres;
        $this->type     = $type;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getVille(): string
    {
        return $this->ville;
    }

    public function getSurface(): float
    {
        return $this->surface;
    }

    public function getChambres(): int
    {
        return $this->chambres;
    }

    public function getType(): ?TypeBien
    {
        return $this->type;
    }

    abstract public function getCategorie(): CategorieBien;

    abstract public function getAttributsSpecifiques(): array;

    abstract public static function fromArray(array $row): static;

    protected static function typeFromRow(array $row): ?TypeBien
    {
        $value = $row['type'] ?? null;
        if ($value === null || $value === '') {
            return null;
        }

        return TypeBien::tryFrom((string) $value);
    }
}
