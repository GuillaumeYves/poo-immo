<?php

declare(strict_types=1);

namespace App\Entity;

interface AnnonceRepositoryInterface
{
    public function add(Annonce $annonce): void;

    /** @return Annonce[] */
    public function findAll(): array;

    public function findOneByVille(string $ville): ?Annonce;

    /** @return Annonce[] */
    public function findByVille(string $ville): array;

    /** @return Annonce[] */
    public function findByTransaction(string $type): array;

    /** @return Annonce[] */
    public function findByTypeBien(string $type): array;

    public function count(): int;
}
