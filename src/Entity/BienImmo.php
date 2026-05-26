<?php

declare(strict_types=1);

abstract class BienImmo
{
    protected string $ville;
    protected float $surface;
    protected int $chambres;
    protected ?string $description = null;

    public function __construct(string $ville, int|float $surface, int $chambres, ?string $description = null)
    {
        $this->setVille($ville);
        $this->setSurface($surface);
        $this->setChambres($chambres);
        $this->setDescription($description);
    }

    public function getVille(): string
    {
        return $this->ville;
    }

    public function setVille(string $ville): void
    {
        if (trim($ville) === '') {
            throw new InvalidArgumentException('La ville ne peut pas être vide.');
        }
        $this->ville = $ville;
    }

    public function getSurface(): float
    {
        return $this->surface;
    }

    public function setSurface(int|float $surface): void
    {
        if ($surface <= 0) {
            throw new InvalidArgumentException('La surface ne peut pas être négative ou égale à zéro.');
        }
        $this->surface = (float) $surface;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description !== null && trim($description) === '' ? null : $description;
    }

    public function getChambres(): int
    {
        return $this->chambres;
    }

    public function setChambres(int $chambres): void
    {
        if ($chambres < 0) {
            throw new InvalidArgumentException('Le nombre de chambres ne peut pas être négatif.');
        }
        $this->chambres = $chambres;
    }

    abstract public function getType(): string;
}
