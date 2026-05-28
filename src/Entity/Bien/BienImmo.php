<?php

declare(strict_types=1);

namespace App\Entity\Bien;

use InvalidArgumentException;

abstract class BienImmo
{
    protected readonly string $id;
    protected readonly string $ville;
    protected readonly float $surface;
    protected readonly int $chambres;
    protected readonly ?string $description;

    public function __construct(string $id, string $ville, int|float $surface, int $chambres, ?string $description = null)
    {
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

        $this->id          = $id;
        $this->ville       = $ville;
        $this->surface     = (float) $surface;
        $this->chambres    = $chambres;
        $this->description = $description !== null && trim($description) === '' ? null : $description;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    abstract public function getType(): string;

    /**
     * @param array<string, mixed> $row
     */
    abstract public static function fromArray(array $row): static;
}
