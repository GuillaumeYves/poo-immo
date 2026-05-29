<?php

declare(strict_types=1);

namespace App\Model\Bien;

use RuntimeException;

/* *
 * Factory pour créer des instances de biens à partir de données brutes, en
 * déterminant dynamiquement les classes de biens à instancier en fonction des
 * données fournies.
 */
final class BienFactory
{
    private const CLASSES = [
        CategorieBien::Appartement->value => Appartement::class,
        CategorieBien::Maison->value      => Maison::class,
        CategorieBien::Villa->value       => Villa::class,
    ];

    public function hydrateAll(array $rows): array
    {
        return array_map(fn(array $row): Bien => $this->hydrate($row), $rows);
    }

    public function hydrate(array $row): Bien
    {
        $categorie = (string) ($row['categorie'] ?? throw new RuntimeException('Row sans catégorie de bien.'));
        $class = self::CLASSES[$categorie]
            ?? throw new RuntimeException("Catégorie de bien inconnue : {$categorie}");

        return $class::fromArray($row);
    }
}
