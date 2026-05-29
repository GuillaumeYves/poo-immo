<?php

declare(strict_types=1);

namespace App\Model\Annonce;

/* *
 * Interface pour le dépôt d'annonces immobilières, définissant les méthodes
 * nécessaires pour gérer les annonces, telles que la recherche, la création,
 * la mise à jour et la suppression.
 */
interface AnnonceRepository
{
    public function findAll(): array;

    public function findById(int $id): ?Annonce;

    public function findByFilters(array $filters): array;

    public function updatePrixCourant(int $id, string $nouveauPrix): void;

    public function create(array $data): int;

    public function update(int $id, array $data): void;

    public function delete(int $id): void;
}
