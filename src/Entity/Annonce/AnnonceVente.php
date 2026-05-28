<?php

declare(strict_types=1);

namespace App\Entity\Annonce;

use App\Entity\Bien\BienImmo;
use App\Formatter\MoneyFormatter;
use DateTimeImmutable;
use InvalidArgumentException;
use RuntimeException;

class AnnonceVente extends Annonce
{
    // Montant en string : précision exacte via BCMath (cf README - section Persistance).
    protected readonly string $prix;

    public function __construct(
        BienImmo $bien,
        string $prix,
        ?DateTimeImmutable $datePublication = null,
        EtatAnnonce $etat = EtatAnnonce::Disponible,
    ) {
        parent::__construct($bien, $datePublication, $etat);

        if (bccomp($prix, '0', 2) <= 0) {
            throw new InvalidArgumentException('Le prix de vente ne peut pas être négatif ou égal à zéro.');
        }
        $this->prix = $prix;
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function fromArray(array $row, BienImmo $bien, EtatAnnonce $etat, ?DateTimeImmutable $datePublication): static
    {
        return new self(
            $bien,
            (string) ($row['prix'] ?? throw new RuntimeException('AnnonceVente sans prix.')),
            $datePublication,
            $etat,
        );
    }

    public function getPrix(): string
    {
        return $this->prix;
    }

    public function getPrixM2(): string
    {
        return bcdiv($this->prix, (string) $this->bien->getSurface(), 2);
    }

    public function calculerRentabilite(string $loyerMensuel): string
    {
        $loyerAnnuel = bcmul($loyerMensuel, '12', 2);
        $taux        = bcdiv($loyerAnnuel, $this->prix, 6);

        return bcmul($taux, '100', 2);
    }

    public function getTypeTransaction(): string
    {
        return 'Vente';
    }

    public function getMontant(): string
    {
        return $this->prix;
    }

    public function getAttributsAffichage(MoneyFormatter $formatter): array
    {
        return [
            ['Prix',       $formatter->format($this->prix)],
            ['Prix au m²', $formatter->format($this->getPrixM2()) . '/m²'],
        ];
    }

    public function toExportRow(): array
    {
        return ['prix' => $this->prix];
    }
}
