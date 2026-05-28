<?php

declare(strict_types=1);

namespace App\Entity\Annonce;

use App\Entity\Bien\BienImmo;
use DateTimeImmutable;
use InvalidArgumentException;
use RuntimeException;

class AnnonceVente extends Annonce
{
    protected readonly string $prixInitial;
    protected readonly string $prixCourant;

    public function __construct(
        int $id,
        BienImmo $bien,
        string $prixInitial,
        string $prixCourant,
        ?DateTimeImmutable $datePublication = null,
        EtatAnnonce $etat = EtatAnnonce::Disponible,
    ) {
        parent::__construct($id, $bien, $datePublication, $etat);

        if (bccomp($prixInitial, '0', 2) <= 0) {
            throw new InvalidArgumentException('Le prix initial ne peut pas être négatif ou égal à zéro.');
        }
        if (bccomp($prixCourant, '0', 2) <= 0) {
            throw new InvalidArgumentException('Le prix courant ne peut pas être négatif ou égal à zéro.');
        }
        if (bccomp($prixCourant, $prixInitial, 2) > 0) {
            throw new InvalidArgumentException('Le prix courant ne peut pas dépasser le prix initial.');
        }

        $this->prixInitial = $prixInitial;
        $this->prixCourant = $prixCourant;
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function fromArray(array $row, BienImmo $bien, EtatAnnonce $etat, ?DateTimeImmutable $datePublication): static
    {
        return new self(
            (int) ($row['annonce_id'] ?? throw new RuntimeException('Annonce sans id.')),
            $bien,
            (string) ($row['prix_initial']        ?? throw new RuntimeException('AnnonceVente sans prix_initial.')),
            (string) ($row['prix_avec_reduction'] ?? throw new RuntimeException('AnnonceVente sans prix_avec_reduction.')),
            $datePublication,
            $etat,
        );
    }

    public function getPrixInitial(): string
    {
        return $this->prixInitial;
    }

    public function getPrixCourant(): string
    {
        return $this->prixCourant;
    }

    public function aUneReduction(): bool
    {
        return bccomp($this->prixCourant, $this->prixInitial, 2) < 0;
    }

    public function getReductionPourcentage(): string
    {
        if (!$this->aUneReduction()) {
            return '0';
        }

        $facteur = bcdiv($this->prixCourant, $this->prixInitial, 6);
        $taux    = bcsub('1', $facteur, 6);

        return bcmul($taux, '100', 2);
    }

    public function getPrixM2(): string
    {
        return bcdiv($this->prixCourant, (string) $this->bien->getSurface(), 2);
    }

    public function calculerRentabilite(string $loyerMensuel): string
    {
        $loyerAnnuel = bcmul($loyerMensuel, '12', 2);
        $taux        = bcdiv($loyerAnnuel, $this->prixCourant, 6);

        return bcmul($taux, '100', 2);
    }

    public function getTypeTransaction(): string
    {
        return 'vente';
    }
}
