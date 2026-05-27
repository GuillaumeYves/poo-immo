<?php

declare(strict_types=1);

namespace App\Entity;

use DateTimeImmutable;
use InvalidArgumentException;

class AnnonceVente extends Annonce
{
    protected readonly float $prix;

    public function __construct(
        BienImmo $bien,
        int|float $prix,
        ?DateTimeImmutable $datePublication = null,
        EtatAnnonce $etat = EtatAnnonce::Disponible,
    ) {
        parent::__construct($bien, $datePublication, $etat);

        if ($prix <= 0) {
            throw new InvalidArgumentException('Le prix de vente ne peut pas être négatif ou égal à zéro.');
        }
        $this->prix = (float) $prix;
    }

    public function getPrix(): float
    {
        return $this->prix;
    }

    public function getPrixM2(): float
    {
        return $this->prix / $this->bien->getSurface();
    }

    public function calculerRentabilite(int|float $loyerMensuel): float
    {
        return (((float) $loyerMensuel * 12) / $this->prix) * 100;
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
