<?php

declare(strict_types=1);

namespace App\Database;

use App\Entity\Appartement;
use App\Entity\BienImmo;
use App\Entity\Maison;
use JsonException;
use RuntimeException;

final class BienSeedLoader
{
    /**
     * @return array<string, BienImmo> Biens indexés par leur id.
     */
    public static function load(string $path): array
    {
        if (!is_file($path) || !is_readable($path)) {
            throw new RuntimeException("Seed biens introuvable ou illisible : {$path}");
        }

        $raw = file_get_contents($path);
        if ($raw === false) {
            throw new RuntimeException("Impossible de lire le seed biens : {$path}");
        }

        try {
            $rows = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new RuntimeException("Seed biens JSON invalide : {$e->getMessage()}", previous: $e);
        }

        if (!is_array($rows)) {
            throw new RuntimeException('Seed biens invalide : tableau attendu à la racine.');
        }

        $biens = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                throw new RuntimeException('Entrée de bien invalide (objet attendu).');
            }

            $id = (string) ($row['id'] ?? throw new RuntimeException('Bien sans id.'));
            if (isset($biens[$id])) {
                throw new RuntimeException("Id de bien dupliqué : {$id}");
            }

            $biens[$id] = self::hydrate($row);
        }

        return $biens;
    }

    /**
     * @param array<string, mixed> $row
     */
    private static function hydrate(array $row): BienImmo
    {
        $ville       = (string) ($row['ville']    ?? throw new RuntimeException('Bien sans ville.'));
        $surface     = $row['surface']             ?? throw new RuntimeException('Bien sans surface.');
        $chambres    = (int) ($row['chambres']     ?? throw new RuntimeException('Bien sans chambres.'));
        $description = isset($row['description']) ? (string) $row['description'] : null;

        return match ($row['type'] ?? null) {
            'appartement' => new Appartement(
                $ville,
                $surface,
                $chambres,
                (int) ($row['etage'] ?? 0),
                $description,
            ),
            'maison' => new Maison(
                $ville,
                $surface,
                $chambres,
                $row['terrain'] ?? throw new RuntimeException('Maison sans terrain.'),
                $description,
            ),
            default => throw new RuntimeException('Type de bien inconnu : ' . var_export($row['type'] ?? null, true)),
        };
    }
}
