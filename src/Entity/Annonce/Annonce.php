<?php

declare(strict_types=1);

namespace App\Entity\Annonce;

use App\Entity\Bien\BienImmo;
use DateTimeImmutable;
use InvalidArgumentException;

abstract class Annonce
{
    protected readonly int $id;
    protected readonly BienImmo $bien;
    protected readonly DateTimeImmutable $datePublication;
    protected EtatAnnonce $etat;

    public function __construct(
        int $id,
        BienImmo $bien,
        ?DateTimeImmutable $datePublication = null,
        EtatAnnonce $etat = EtatAnnonce::Disponible,
    ) {
        if ($id <= 0) {
            throw new InvalidArgumentException("L'id d'une annonce doit être strictement positif.");
        }

        $this->id              = $id;
        $this->bien            = $bien;
        $this->datePublication = $datePublication ?? new DateTimeImmutable();
        $this->etat            = $etat;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getBien(): BienImmo
    {
        return $this->bien;
    }

    public function getDatePublication(): DateTimeImmutable
    {
        return $this->datePublication;
    }

    public function getEtat(): EtatAnnonce
    {
        return $this->etat;
    }

    public function setEtat(EtatAnnonce $etat): void
    {
        $this->etat = $etat;
    }

    abstract public function getTypeTransaction(): string;

    /**
     * @param array<string, mixed> $row
     */
    abstract public static function fromArray(
        array $row,
        BienImmo $bien,
        EtatAnnonce $etat,
        ?DateTimeImmutable $datePublication,
    ): static;
}
