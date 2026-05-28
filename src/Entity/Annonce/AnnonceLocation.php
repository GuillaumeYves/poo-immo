<?php

declare(strict_types=1);

namespace App\Entity\Annonce;

use App\Entity\Bien\BienImmo;
use DateTimeImmutable;
use InvalidArgumentException;
use RuntimeException;

class AnnonceLocation extends Annonce
{
    protected readonly string $loyer;
    protected readonly string $charges;

    public function __construct(
        int $id,
        BienImmo $bien,
        string $loyer,
        string $charges = '0',
        ?DateTimeImmutable $datePublication = null,
        EtatAnnonce $etat = EtatAnnonce::Disponible,
    ) {
        parent::__construct($id, $bien, $datePublication, $etat);

        if (bccomp($loyer, '0', 2) <= 0) {
            throw new InvalidArgumentException('Le loyer doit être strictement positif.');
        }
        if (bccomp($charges, '0', 2) < 0) {
            throw new InvalidArgumentException('Les charges ne peuvent pas être négatives.');
        }

        $this->loyer   = $loyer;
        $this->charges = $charges;
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function fromArray(array $row, BienImmo $bien, EtatAnnonce $etat, ?DateTimeImmutable $datePublication): static
    {
        return new self(
            (int) ($row['annonce_id'] ?? throw new RuntimeException('Annonce sans id.')),
            $bien,
            (string) ($row['loyer'] ?? throw new RuntimeException('AnnonceLocation sans loyer.')),
            (string) ($row['charges'] ?? '0'),
            $datePublication,
            $etat,
        );
    }

    public function getLoyer(): string
    {
        return $this->loyer;
    }

    public function getCharges(): string
    {
        return $this->charges;
    }

    public function getLoyerCharges(): string
    {
        return bcadd($this->loyer, $this->charges, 2);
    }

    public function getTypeTransaction(): string
    {
        return 'location';
    }
}
