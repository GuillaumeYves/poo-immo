<?php

declare(strict_types=1);

namespace App\Entity\Annonce;

use App\Entity\Bien\Appartement;
use App\Entity\Bien\BienImmo;
use App\Entity\Bien\Maison;
use DateTimeImmutable;
use RuntimeException;


final class AnnonceFactory
{
    /** @var array<string, class-string<BienImmo>> */
    private const BIEN_TYPES = [
        'appartement' => Appartement::class,
        'maison'      => Maison::class,
    ];

    /** @var array<string, class-string<Annonce>> */
    private const ANNONCE_TYPES = [
        'vente'    => AnnonceVente::class,
        'location' => AnnonceLocation::class,
    ];

    /**
     * @param array<int, array<string, mixed>> $rows
     * @return Annonce[]
     */
    public function hydrateAll(array $rows): array
    {
        return array_map(fn(array $row) => $this->hydrate($row), $rows);
    }

    /**
     * @param array<string, mixed> $row
     */
    public function hydrate(array $row): Annonce
    {
        $bien    = $this->hydrateBien($row);
        $class   = $this->resolveAnnonceClass($row);
        $etat    = EtatAnnonce::from((string) $row['etat']);
        $date    = isset($row['datePublication']) && $row['datePublication'] !== null
            ? new DateTimeImmutable((string) $row['datePublication'])
            : null;

        return $class::fromArray($row, $bien, $etat, $date);
    }

    /**
     * @param array<string, mixed> $row
     */
    private function hydrateBien(array $row): BienImmo
    {
        $type  = (string) ($row['type'] ?? throw new RuntimeException('Row sans type de bien.'));
        $class = self::BIEN_TYPES[$type]
            ?? throw new RuntimeException("Type de bien inconnu : {$type}");

        return $class::fromArray($row);
    }

    /**
     * @param array<string, mixed> $row
     * @return class-string<Annonce>
     */
    private function resolveAnnonceClass(array $row): string
    {
        $transaction = (string) ($row['transaction'] ?? throw new RuntimeException('Row sans transaction.'));

        return self::ANNONCE_TYPES[$transaction]
            ?? throw new RuntimeException("Transaction inconnue : {$transaction}");
    }
}
