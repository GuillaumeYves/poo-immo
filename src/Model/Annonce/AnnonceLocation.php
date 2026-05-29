<?php

declare(strict_types=1);

namespace App\Model\Annonce;

use App\Model\Bien\Bien;
use App\Model\Formatter\MoneyFormatter;
use DateTimeImmutable;
use InvalidArgumentException;
use RuntimeException;

/* *
 * Représente une annonce de location immobilière, avec des informations spécifiques
 * telles que le loyer, les charges, etc., en plus des informations générales d'une annonce.
 */
class AnnonceLocation extends Annonce
{
    protected readonly string $loyerInitial;
    protected readonly string $chargesInitiales;
    protected readonly string $loyer;
    protected readonly string $charges;

    public function __construct(
        int $id,
        Bien $bien,
        string $loyerInitial,
        string $loyer,
        string $chargesInitiales = '0',
        string $charges = '0',
        ?DateTimeImmutable $datePublication = null,
        EtatAnnonce $etat = EtatAnnonce::Disponible,
        ?DateTimeImmutable $derniereModification = null,
        ?string $titre = null,
        ?string $description = null,
    ) {
        parent::__construct($id, $bien, $datePublication, $etat, $derniereModification, $titre, $description);

        if (bccomp($loyerInitial, '0', 2) <= 0) {
            throw new InvalidArgumentException('Le loyer initial doit être strictement positif.');
        }
        if (bccomp($loyer, '0', 2) <= 0) {
            throw new InvalidArgumentException('Le loyer doit être strictement positif.');
        }
        if (bccomp($chargesInitiales, '0', 2) < 0) {
            throw new InvalidArgumentException('Les charges initiales ne peuvent pas être négatives.');
        }
        if (bccomp($charges, '0', 2) < 0) {
            throw new InvalidArgumentException('Les charges ne peuvent pas être négatives.');
        }

        $this->loyerInitial     = $loyerInitial;
        $this->loyer            = $loyer;
        $this->chargesInitiales = $chargesInitiales;
        $this->charges          = $charges;
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
        $loyer   = (string) ($row['loyer']   ?? throw new RuntimeException('AnnonceLocation sans loyer.'));
        $charges = (string) ($row['charges'] ?? '0');

        return new self(
            (int) ($row['annonce_id'] ?? throw new RuntimeException('Annonce sans id.')),
            $bien,
            (string) ($row['loyer_initial']     ?? $loyer),
            $loyer,
            (string) ($row['charges_initiales'] ?? $charges),
            $charges,
            $datePublication,
            $etat,
            $derniereModification,
            $titre,
            $description,
        );
    }

    public function getLoyerInitial(): string
    {
        return $this->loyerInitial;
    }

    public function getLoyer(): string
    {
        return $this->loyer;
    }

    public function getChargesInitiales(): string
    {
        return $this->chargesInitiales;
    }

    public function getCharges(): string
    {
        return $this->charges;
    }

    public function getLoyerCharges(): string
    {
        return bcadd($this->loyer, $this->charges, 2);
    }

    public function getLoyerChargesInitial(): string
    {
        return bcadd($this->loyerInitial, $this->chargesInitiales, 2);
    }

    public function getPrixVariation(): PrixVariation
    {
        return new PrixVariation(
            $this->getLoyerCharges(),
            $this->getLoyerChargesInitial(),
            '/mois',
        );
    }

    public function getAttributsSpecifiques(MoneyFormatter $formatter): array
    {
        return [
            ['Loyer',   $formatter->format($this->loyer)   . '/mois'],
            ['Charges', $formatter->format($this->charges) . '/mois'],
            ['Total',   $formatter->format($this->getLoyerCharges()) . '/mois'],
        ];
    }

    public function getTypeTransaction(): string
    {
        return 'location';
    }
}
