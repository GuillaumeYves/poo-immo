<?php

require_once __DIR__ . '/Annonce.php';

class AnnonceVente extends Annonce
{
    protected float $prix;

    public function __construct(BienImmo $bien, float $prix, ?DateTimeImmutable $datePublication = null)
    {
        parent::__construct($bien, $datePublication);
        $this->setPrix($prix);
    }

    public function getPrix(): float
    {
        return $this->prix;
    }

    public function setPrix(float $prix): void
    {
        if ($prix <= 0) {
            throw new InvalidArgumentException('Le prix de vente ne peut pas être négatif ou égal à zéro.');
        }
        $this->prix = $prix;
    }

    public function getPrixM2(): float
    {
        return $this->prix / $this->bien->getSurface();
    }

    public function calculerRentabilite(float $loyerMensuel): float
    {
        return (($loyerMensuel * 12) / $this->prix) * 100;
    }

    public function getTypeTransaction(): string
    {
        return 'Vente';
    }

    public function getMontant(): float
    {
        return $this->prix;
    }
}
