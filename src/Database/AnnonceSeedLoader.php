<?php

declare(strict_types=1);

namespace App\Database;

use App\Entity\Annonce;
use App\Entity\AnnonceLocation;
use App\Entity\AnnonceVente;
use App\Entity\BienImmo;
use App\Entity\EtatAnnonce;
use DateTimeImmutable;
use JsonException;
use RuntimeException;

final class AnnonceSeedLoader
{
    /**
     * @param array<string, BienImmo> $biens Biens indexés par id (cf. BienSeedLoader).
     * @return Annonce[]
     */
    public static function load(string $path, array $biens): array
    {
        if (!is_file($path) || !is_readable($path)) {
            throw new RuntimeException("Seed annonces introuvable ou illisible : {$path}");
        }

        $raw = file_get_contents($path);
        if ($raw === false) {
            throw new RuntimeException("Impossible de lire le seed annonces : {$path}");
        }

        try {
            $rows = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new RuntimeException("Seed annonces JSON invalide : {$e->getMessage()}", previous: $e);
        }

        if (!is_array($rows)) {
            throw new RuntimeException('Seed annonces invalide : tableau attendu à la racine.');
        }

        return array_map(
            static fn(mixed $row): Annonce => self::hydrate(
                is_array($row) ? $row : throw new RuntimeException('Entrée d\'annonce invalide (objet attendu).'),
                $biens,
            ),
            $rows,
        );
    }

    /**
     * @param array<string, mixed>  $row
     * @param array<string, BienImmo> $biens
     */
    private static function hydrate(array $row, array $biens): Annonce
    {
        $bienId = (string) ($row['bienId'] ?? throw new RuntimeException('Annonce sans bienId.'));
        $bien   = $biens[$bienId] ?? throw new RuntimeException("Bien inconnu pour l'annonce : {$bienId}");
        $etat   = EtatAnnonce::from((string) ($row['etat'] ?? 'disponible'));
        $date   = isset($row['datePublication']) && $row['datePublication'] !== null
            ? new DateTimeImmutable((string) $row['datePublication'])
            : null;

        return match ($row['transaction'] ?? null) {
            'vente' => new AnnonceVente(
                $bien,
                $row['prix'] ?? throw new RuntimeException('AnnonceVente sans prix.'),
                $date,
                $etat,
            ),
            'location' => new AnnonceLocation(
                $bien,
                $row['loyer'] ?? throw new RuntimeException('AnnonceLocation sans loyer.'),
                $row['charges'] ?? 0,
                $date,
                $etat,
            ),
            default => throw new RuntimeException('Transaction inconnue : ' . var_export($row['transaction'] ?? null, true)),
        };
    }
}
