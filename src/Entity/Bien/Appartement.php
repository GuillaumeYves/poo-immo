<?php

declare(strict_types=1);

namespace App\Entity\Bien;

use InvalidArgumentException;
use RuntimeException;

class Appartement extends BienImmo
{
    protected readonly int $etage;

    public function __construct(string $id, string $ville, int|float $surface, int $chambres, int $etage = 0, ?string $description = null)
    {
        parent::__construct($id, $ville, $surface, $chambres, $description);

        if ($etage < 0) {
            throw new InvalidArgumentException("L'étage ne peut pas être négatif.");
        }
        $this->etage = $etage;
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function fromArray(array $row): static
    {
        return new self(
            (string) ($row['bien_id']  ?? throw new RuntimeException('Bien sans id.')),
            (string) ($row['ville']    ?? throw new RuntimeException('Bien sans ville.')),
            (float) ($row['surface']   ?? throw new RuntimeException('Bien sans surface.')),
            (int) ($row['chambres']    ?? throw new RuntimeException('Bien sans chambres.')),
            (int) ($row['etage'] ?? 0),
            isset($row['description']) ? (string) $row['description'] : null,
        );
    }

    public function getEtage(): int
    {
        return $this->etage;
    }

    public function getType(): string
    {
        return 'appartement';
    }
}
