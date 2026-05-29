<?php

declare(strict_types=1);

namespace App\Model\Annonce;

use App\Model\Bien\Appartement;
use App\Model\Bien\Bien;
use App\Model\Bien\CategorieBien;
use App\Model\Bien\Maison;
use App\Model\Bien\Villa;
use DateTimeImmutable;
use RuntimeException;

/* *
 * Factory pour créer des instances d'annonces à partir de données brutes, en
 * déterminant dynamiquement les classes de biens et d'annonces à instancier
 * en fonction des données fournies.
 */
final class AnnonceFactory
{
    private const BIEN_CLASSES = [
        CategorieBien::Appartement->value => Appartement::class,
        CategorieBien::Maison->value      => Maison::class,
        CategorieBien::Villa->value       => Villa::class,
    ];

    private const ANNONCE_TYPES = [
        'vente'    => AnnonceVente::class,
        'location' => AnnonceLocation::class,
    ];

    public function hydrateAll(array $rows): array
    {
        return array_map(fn(array $row) => $this->hydrate($row), $rows);
    }

    public function hydrate(array $row): Annonce
    {
        $bien        = $this->hydrateBien($row);
        $class       = $this->resolveAnnonceClass($row);
        $etat        = EtatAnnonce::from((string) $row['etat']);
        $date        = $this->parseDate($row['datePublication'] ?? null);
        $modifiee    = $this->parseDate($row['derniereModification'] ?? null);
        $titre       = isset($row['titre']) && $row['titre'] !== null ? (string) $row['titre'] : null;
        $description = isset($row['description']) && $row['description'] !== null ? (string) $row['description'] : null;

        return $class::fromArray($row, $bien, $etat, $date, $modifiee, $titre, $description);
    }

    private function hydrateBien(array $row): Bien
    {
        $categorie = (string) ($row['categorie'] ?? throw new RuntimeException('Row sans catégorie de bien.'));
        $class = self::BIEN_CLASSES[$categorie]
            ?? throw new RuntimeException("Catégorie de bien inconnue : {$categorie}");

        return $class::fromArray($row);
    }

    private function resolveAnnonceClass(array $row): string
    {
        $transaction = (string) ($row['transaction'] ?? throw new RuntimeException('Row sans transaction.'));

        return self::ANNONCE_TYPES[$transaction]
            ?? throw new RuntimeException("Transaction inconnue : {$transaction}");
    }

    private function parseDate(mixed $value): ?DateTimeImmutable
    {
        if ($value === null || $value === '') {
            return null;
        }

        return new DateTimeImmutable((string) $value);
    }
}
