<?php

declare(strict_types=1);

namespace App\Entity\Annonce;

interface AnnonceRepositoryInterface
{
    /** @return Annonce[] */
    public function findAll(): array;

    public function findById(int $id): ?Annonce;

    public function findOneByVille(string $ville): ?Annonce;

    /** @return Annonce[] */
    public function findByVille(string $ville): array;

    /** @return Annonce[] */
    public function findByTransaction(string $type): array;

    /** @return Annonce[] */
    public function findByTypeBien(string $type): array;

    public function count(): int;

    public function updatePrixCourant(int $id, string $nouveauPrix): void;
}
