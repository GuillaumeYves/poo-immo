<?php

declare(strict_types=1);

namespace App\Entity;

use App\Formatter\MoneyFormatter;
use DateTimeImmutable;

abstract class Annonce
{
    protected readonly BienImmo $bien;
    protected readonly DateTimeImmutable $datePublication;
    protected EtatAnnonce $etat;

    public function __construct(
        BienImmo $bien,
        ?DateTimeImmutable $datePublication = null,
        EtatAnnonce $etat = EtatAnnonce::Disponible,
    ) {
        $this->bien            = $bien;
        $this->datePublication = $datePublication ?? new DateTimeImmutable();
        $this->etat            = $etat;
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

    abstract public function getMontant(): float;

    /**
     * @return array<int, array{0: string, 1: string}>
     */
    abstract public function getAttributsAffichage(MoneyFormatter $formatter): array;

    /**
     * @return array<string, int|float|string|null>
     */
    abstract public function toExportRow(): array;

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
