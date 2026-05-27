<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Annonce;

class AnnonceRepository
{
    /** @var Annonce[] */
    private array $annonces = [];

    public function add(Annonce $annonce): void
    {
        $this->annonces[] = $annonce;
    }

    /** @return Annonce[] */
    public function findAll(): array
    {
        return $this->annonces;
    }

    public function findOneByVille(string $ville): ?Annonce
    {
        foreach ($this->annonces as $annonce) {
            if (strcasecmp($annonce->getBien()->getVille(), $ville) === 0) {
                return $annonce;
            }
        }

        return null;
    }

    /** @return Annonce[] */
    public function findByVille(string $ville): array
    {
        return array_values(array_filter(
            $this->annonces,
            fn(Annonce $a) => strcasecmp($a->getBien()->getVille(), $ville) === 0
        ));
    }

    /** @return Annonce[] */
    public function findByTransaction(string $type): array
    {
        return array_values(array_filter(
            $this->annonces,
            fn(Annonce $a) => $a->getTypeTransaction() === $type
        ));
    }

    /** @return Annonce[] */
    public function findByTypeBien(string $type): array
    {
        return array_values(array_filter(
            $this->annonces,
            fn(Annonce $a) => $a->getBien()->getType() === $type
        ));
    }

    public function count(): int
    {
        return count($this->annonces);
    }
}
