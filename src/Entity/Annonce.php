<?php

declare(strict_types=1);

require_once __DIR__ . '/BienImmo.php';
require_once __DIR__ . '/EtatAnnonce.php';

abstract class Annonce
{
    protected BienImmo $bien;
    protected DateTimeImmutable $datePublication;
    protected EtatAnnonce $etat;

    public function __construct(
        BienImmo $bien,
        ?DateTimeImmutable $datePublication = null,
        EtatAnnonce $etat = EtatAnnonce::Disponible,
    ) {
        $this->bien = $bien;
        $this->datePublication = $datePublication ?? new DateTimeImmutable();
        $this->etat = $etat;
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
}
