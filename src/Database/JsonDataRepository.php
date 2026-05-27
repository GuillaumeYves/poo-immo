<?php

declare(strict_types=1);

namespace App\Database;

use App\Entity\Annonce;
use App\Entity\AnnonceLocation;
use App\Entity\AnnonceRepositoryInterface;
use App\Entity\AnnonceVente;
use App\Entity\Appartement;
use App\Entity\BienImmo;
use App\Entity\EtatAnnonce;
use App\Entity\Maison;
use DateTimeImmutable;
use JsonException;
use RuntimeException;

final class JsonDataRepository implements AnnonceRepositoryInterface
{
    /**
     * @var array<string, class-string<BienImmo>>
     */
    private const BIEN_TYPES = [
        'appartement' => Appartement::class,
        'maison'      => Maison::class,
    ];

    /**
     * @var array<string, class-string<Annonce>>
     */
    private const ANNONCE_TYPES = [
        'vente'    => AnnonceVente::class,
        'location' => AnnonceLocation::class,
    ];

    /** @var Annonce[] */
    private array $annonces = [];

    public function __construct(string $dataDir)
    {
        $biens = $this->loadBiens($dataDir . DIRECTORY_SEPARATOR . 'biens.seed.json');
        foreach ($this->loadAnnonces($dataDir . DIRECTORY_SEPARATOR . 'annonces.seed.json', $biens) as $annonce) {
            $this->add($annonce);
        }
    }

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

    /**
     * @return array<string, BienImmo>
     */
    private function loadBiens(string $path): array
    {
        $biens = [];
        foreach ($this->readJsonFile($path, 'biens') as $row) {
            $id = (string) ($row['id'] ?? throw new RuntimeException('Bien sans id.'));
            if (isset($biens[$id])) {
                throw new RuntimeException("Id de bien dupliqué : {$id}");
            }

            $type  = (string) ($row['type'] ?? throw new RuntimeException('Bien sans type.'));
            $class = self::BIEN_TYPES[$type] ?? throw new RuntimeException("Type de bien inconnu : {$type}");

            $biens[$id] = $class::fromArray($row);
        }

        return $biens;
    }

    /**
     * @param array<string, BienImmo> $biens
     * @return Annonce[]
     */
    private function loadAnnonces(string $path, array $biens): array
    {
        $annonces = [];
        foreach ($this->readJsonFile($path, 'annonces') as $row) {
            $bienId      = (string) ($row['bienId']      ?? throw new RuntimeException('Annonce sans bienId.'));
            $bien        = $biens[$bienId]               ?? throw new RuntimeException("Bien inconnu pour l'annonce : {$bienId}");
            $transaction = (string) ($row['transaction'] ?? throw new RuntimeException('Annonce sans transaction.'));
            $class       = self::ANNONCE_TYPES[$transaction] ?? throw new RuntimeException("Transaction inconnue : {$transaction}");

            $etat = EtatAnnonce::from((string) ($row['etat'] ?? 'disponible'));
            $date = isset($row['datePublication']) && $row['datePublication'] !== null
                ? new DateTimeImmutable((string) $row['datePublication'])
                : null;

            $annonces[] = $class::fromArray($row, $bien, $etat, $date);
        }

        return $annonces;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function readJsonFile(string $path, string $label): array
    {
        if (!is_file($path) || !is_readable($path)) {
            throw new RuntimeException("Seed {$label} introuvable ou illisible : {$path}");
        }

        $raw = file_get_contents($path);
        if ($raw === false) {
            throw new RuntimeException("Impossible de lire le seed {$label} : {$path}");
        }

        try {
            $rows = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new RuntimeException("Seed {$label} JSON invalide : {$e->getMessage()}", previous: $e);
        }

        if (!is_array($rows)) {
            throw new RuntimeException("Seed {$label} invalide : tableau attendu à la racine.");
        }

        foreach ($rows as $row) {
            if (!is_array($row)) {
                throw new RuntimeException("Entrée de {$label} invalide (objet attendu).");
            }
        }

        /** @var array<int, array<string, mixed>> $rows */
        return $rows;
    }
}
