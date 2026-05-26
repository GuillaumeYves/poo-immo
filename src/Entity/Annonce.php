<?php

require_once __DIR__ . '/BienImmo.php';

abstract class Annonce
{
    protected BienImmo $bien;
    protected DateTimeImmutable $datePublication;

    public function __construct(BienImmo $bien, ?DateTimeImmutable $datePublication = null)
    {
        $this->bien = $bien;
        $this->datePublication = $datePublication ?? new DateTimeImmutable();
    }

    public function getBien(): BienImmo
    {
        return $this->bien;
    }

    public function getDatePublication(): DateTimeImmutable
    {
        return $this->datePublication;
    }

    abstract public function getTypeTransaction(): string;

    abstract public function getMontant(): float;
}
