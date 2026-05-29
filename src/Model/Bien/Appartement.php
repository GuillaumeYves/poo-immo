<?php

declare(strict_types=1);

namespace App\Model\Bien;

use InvalidArgumentException;
use RuntimeException;

/* *
 * Représente un appartement, qui est un type de bien immobilier, avec des
 * informations spécifiques telles que l'étage, en plus des informations générales d'un bien.
 */
class Appartement extends Bien
{
    protected readonly int $etage;

    public function __construct(
        string $id,
        string $ville,
        int|float $surface,
        int $chambres,
        int $etage = 0,
        ?TypeBien $type = null,
    ) {
        parent::__construct($id, $ville, $surface, $chambres, $type);

        if ($etage < 0) {
            throw new InvalidArgumentException("L'étage ne peut pas être négatif.");
        }
        $this->etage = $etage;
    }

    public static function fromArray(array $row): static
    {
        return new static(
            (string) ($row['bien_id'] ?? throw new RuntimeException('Bien sans id.')),
            (string) ($row['ville']   ?? throw new RuntimeException('Bien sans ville.')),
            (float) ($row['surface']  ?? throw new RuntimeException('Bien sans surface.')),
            (int) ($row['chambres']   ?? throw new RuntimeException('Bien sans chambres.')),
            (int) ($row['etage'] ?? 0),
            self::typeFromRow($row),
        );
    }

    public function getEtage(): int
    {
        return $this->etage;
    }

    public function getCategorie(): CategorieBien
    {
        return CategorieBien::Appartement;
    }

    public function getAttributsSpecifiques(): array
    {
        return [
            ['Étage', (string) $this->etage],
        ];
    }
}
