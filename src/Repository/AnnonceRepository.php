<?php

require_once __DIR__ . '/../Entity/Annonce.php';

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
