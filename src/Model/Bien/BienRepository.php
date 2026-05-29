<?php

declare(strict_types=1);

namespace App\Model\Bien;

/* *
 * Interface pour le dépôt de biens immobiliers, définissant les méthodes nécessaires
 * pour gérer les biens, telles que la recherche de biens non liés à des annonces,
 * la création de nouveaux biens, etc.
 */
interface BienRepository
{
    public function findUnlinked(): array;

    public function findUnlinkedById(string $id): ?Bien;

    public function create(array $data): string;
}
