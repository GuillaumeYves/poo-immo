<?php

declare(strict_types=1);

namespace App\Entity;

use App\Formatter\MoneyFormatter;
use DateTimeImmutable;
use InvalidArgumentException;
use RuntimeException;

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

    /**
     * @param array<string, mixed> $row
     */
    public static function fromArray(array $row, BienImmo $bien, EtatAnnonce $etat, ?DateTimeImmutable $datePublication): static
    {
        return new self(
            $bien,
            $row['prix'] ?? throw new RuntimeException('AnnonceVente sans prix.'),
            $datePublication,
            $etat,
        );
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
