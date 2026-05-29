<?php

declare(strict_types=1);

namespace App\Model\Annonce;

use App\Model\Bien\Bien;
use App\Model\Formatter\MoneyFormatter;
use DateTimeImmutable;
use InvalidArgumentException;
use RuntimeException;

/* *
 * Représente une annonce de vente immobilière, avec des informations spécifiques
 * telles que le prix initial, le prix courant, etc., en plus des informations générales d'une annonce.
 */
class AnnonceVente extends Annonce
{
    protected readonly string $prixInitial;
    protected readonly string $prixCourant;

    public function __construct(
        int $id,
        Bien $bien,
        string $prixInitial,
        string $prixCourant,
        ?DateTimeImmutable $datePublication = null,
        EtatAnnonce $etat = EtatAnnonce::Disponible,
        ?DateTimeImmutable $derniereModification = null,
        ?string $titre = null,
        ?string $description = null,
    ) {
        parent::__construct($id, $bien, $datePublication, $etat, $derniereModification, $titre, $description);

        if (bccomp($prixInitial, '0', 2) <= 0) {
            throw new InvalidArgumentException('Le prix initial ne peut pas être négatif ou égal à zéro.');
        }
        if (bccomp($prixCourant, '0', 2) <= 0) {
            throw new InvalidArgumentException('Le prix courant ne peut pas être négatif ou égal à zéro.');
        }

        $this->prixInitial = $prixInitial;
        $this->prixCourant = $prixCourant;
    }

    public static function fromArray(
        array $row,
        Bien $bien,
        EtatAnnonce $etat,
        ?DateTimeImmutable $datePublication,
        ?DateTimeImmutable $derniereModification,
        ?string $titre,
        ?string $description,
    ): static {
        return new self(
            (int) ($row['annonce_id'] ?? throw new RuntimeException('Annonce sans id.')),
            $bien,
            (string) ($row['prix_initial'] ?? throw new RuntimeException('AnnonceVente sans prix_initial.')),
            (string) ($row['prix_courant'] ?? throw new RuntimeException('AnnonceVente sans prix_courant.')),
            $datePublication,
            $etat,
            $derniereModification,
            $titre,
            $description,
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

    public function getPrixVariation(): PrixVariation
    {
        return new PrixVariation($this->prixCourant, $this->prixInitial);
    }

    public function getPrixM2(): string
    {
        return bcdiv($this->prixCourant, (string) $this->bien->getSurface(), 2);
    }

    public function getAttributsSpecifiques(MoneyFormatter $formatter): array
    {
        return [
            ['Prix au m²', $formatter->format($this->getPrixM2()) . '/m²'],
        ];
    }

    public function getTypeTransaction(): string
    {
        return 'vente';
    }
}
