<?php

declare(strict_types=1);

namespace App\Model\Repository;

use App\Model\Bien\Bien;
use App\Model\Bien\BienFactory;
use App\Model\Bien\BienRepository;
use PDO;

/* *
 * Implémentation de BienRepository utilisant PDO pour interagir avec une base
 * de données relationnelle. Cette classe fournit des méthodes pour rechercher
 * des biens immobiliers non liés à des annonces, trouver un bien non lié par
 * son ID et créer de nouveaux biens, en utilisant des requêtes SQL préparées
 * pour garantir la sécurité et la performance des opérations sur la base de
 * données.
 */
final class PdoBienRepository implements BienRepository
{
    private const BASE_SELECT = <<<'SQL'
        SELECT  b.id        AS bien_id,
                b.categorie AS categorie,
                b.type      AS type,
                b.ville     AS ville,
                b.surface   AS surface,
                b.chambres  AS chambres,
                b.etage     AS etage,
                b.terrain   AS terrain
        FROM    biens b
        SQL;

    private readonly PDO $pdo;

    public function __construct(
        Database $database,
        private readonly BienFactory $factory,
    ) {
        $this->pdo = $database->pdo();
    }

    public function findUnlinked(): array
    {
        $stmt = $this->pdo->query(
            self::BASE_SELECT
            . ' LEFT JOIN annonces a ON a.bien_id = b.id'
            . ' WHERE a.id IS NULL'
            . ' ORDER BY b.ville, b.id'
        );

        return $this->factory->hydrateAll($stmt->fetchAll());
    }

    public function findUnlinkedById(string $id): ?Bien
    {
        $stmt = $this->pdo->prepare(
            self::BASE_SELECT
            . ' LEFT JOIN annonces a ON a.bien_id = b.id'
            . ' WHERE b.id = :id AND a.id IS NULL'
            . ' LIMIT 1'
        );
        $stmt->execute(['id' => $id]);

        $row = $stmt->fetch();

        return $row === false ? null : $this->factory->hydrate($row);
    }

    public function create(array $data): string
    {
        $id = $this->generateId((string) $data['ville']);

        $stmt = $this->pdo->prepare(<<<'SQL'
            INSERT INTO biens (id, categorie, type, ville, surface, chambres, etage, terrain)
            VALUES (:id, :categorie, :type, :ville, :surface, :chambres, :etage, :terrain)
            SQL);

        $stmt->execute([
            'id'        => $id,
            'categorie' => $data['categorie'],
            'type'      => $data['type'] ?? null,
            'ville'     => $data['ville'],
            'surface'   => $data['surface'],
            'chambres'  => $data['chambres'],
            'etage'     => $data['etage'],
            'terrain'   => $data['terrain'],
        ]);

        return $id;
    }

    private function generateId(string $ville): string
    {
        $base = strtolower(trim($ville));
        $base = preg_replace('/[^a-z0-9]+/', '-', $base) ?: 'bien';
        $base = trim($base, '-') ?: 'bien';

        do {
            $id = $base . '-' . bin2hex(random_bytes(4));
        } while ($this->exists($id));

        return $id;
    }

    private function exists(string $id): bool
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM biens WHERE id = :id');
        $stmt->execute(['id' => $id]);

        return (int) $stmt->fetchColumn() > 0;
    }
}
