<?php

declare(strict_types=1);

namespace App\Entity;

use App\Formatter\MoneyFormatter;
use DateTimeImmutable;
use InvalidArgumentException;
use RuntimeException;

class AnnonceLocation extends Annonce
{
    protected readonly float $loyer;
    protected readonly float $charges;

    public function __construct(
        BienImmo $bien,
        int|float $loyer,
        int|float $charges = 0.0,
        ?DateTimeImmutable $datePublication = null,
        EtatAnnonce $etat = EtatAnnonce::Disponible,
    ) {
        parent::__construct($bien, $datePublication, $etat);

        if ($loyer <= 0) {
            throw new InvalidArgumentException('Le loyer doit être strictement positif.');
        }
        if ($charges < 0) {
            throw new InvalidArgumentException('Les charges ne peuvent pas être négatives.');
        }

        $this->loyer   = (float) $loyer;
        $this->charges = (float) $charges;
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function fromArray(array $row, BienImmo $bien, EtatAnnonce $etat, ?DateTimeImmutable $datePublication): static
    {
        return new self(
            $bien,
            $row['loyer'] ?? throw new RuntimeException('AnnonceLocation sans loyer.'),
            $row['charges'] ?? 0,
            $datePublication,
            $etat,
        );
    }

    public function getLoyer(): float
    {
        return $this->loyer;
    }

    public function getCharges(): float
    {
        return $this->charges;
    }

    public function getLoyerCharges(): float
    {
        return $this->loyer + $this->charges;
    }

    public function getTypeTransaction(): string
    {
        return 'Location';
    }

    public function getMontant(): float
    {
        return $this->loyer;
    }

    public function getAttributsAffichage(MoneyFormatter $formatter): array
    {
        return [
            ['Loyer',   $formatter->format($this->loyer)            . '/mois'],
            ['Charges', $formatter->format($this->charges)          . '/mois'],
            ['Total',   $formatter->format($this->getLoyerCharges()) . '/mois'],
        ];
    }

    public function toExportRow(): array
    {
        return [
            'loyer'   => $this->loyer,
            'charges' => $this->charges,
        ];
    }
}
