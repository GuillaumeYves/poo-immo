<?php

declare(strict_types=1);

namespace App\Model\Annonce;

use App\Model\Bien\Bien;
use App\Model\Formatter\MoneyFormatter;
use DateTimeImmutable;
use InvalidArgumentException;

/* *
 * Représente une annonce immobilière, associée à un bien, avec des informations
 * telles que le titre, la description, la date de publication, l'état, etc.
 */
abstract class Annonce
{
    protected readonly int $id;
    protected readonly Bien $bien;
    protected readonly ?string $titre;
    protected readonly ?string $description;
    protected readonly DateTimeImmutable $datePublication;
    protected readonly DateTimeImmutable $derniereModification;
    protected readonly EtatAnnonce $etat;

    public function __construct(
        int $id,
        Bien $bien,
        ?DateTimeImmutable $datePublication = null,
        EtatAnnonce $etat = EtatAnnonce::Disponible,
        ?DateTimeImmutable $derniereModification = null,
        ?string $titre = null,
        ?string $description = null,
    ) {
        if ($id <= 0) {
            throw new InvalidArgumentException("L'id d'une annonce doit être strictement positif.");
        }

        $this->id                   = $id;
        $this->bien                 = $bien;
        $this->titre                = $titre !== null && trim($titre) === '' ? null : $titre;
        $this->description          = $description !== null && trim($description) === '' ? null : $description;
        $this->datePublication      = $datePublication ?? new DateTimeImmutable();
        $this->derniereModification = $derniereModification ?? $this->datePublication;
        $this->etat                 = $etat;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getBien(): Bien
    {
        return $this->bien;
    }

    public function getDatePublication(): DateTimeImmutable
    {
        return $this->datePublication;
    }

    public function getDerniereModification(): DateTimeImmutable
    {
        return $this->derniereModification;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getEtat(): EtatAnnonce
    {
        return $this->etat;
    }

    abstract public function getTypeTransaction(): string;

    abstract public function getPrixVariation(): PrixVariation;

    abstract public function getAttributsSpecifiques(MoneyFormatter $formatter): array;

    abstract public static function fromArray(
        array $row,
        Bien $bien,
        EtatAnnonce $etat,
        ?DateTimeImmutable $datePublication,
        ?DateTimeImmutable $derniereModification,
        ?string $titre,
        ?string $description,
    ): static;
}
