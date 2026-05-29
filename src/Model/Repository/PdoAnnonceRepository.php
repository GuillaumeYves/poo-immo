<?php

declare(strict_types=1);

namespace App\Model\Repository;

use App\Model\Annonce\Annonce;
use App\Model\Annonce\AnnonceFactory;
use App\Model\Annonce\AnnonceRepository;
use PDO;
use RuntimeException;
use Throwable;

/* *
 * Implémentation de AnnonceRepository utilisant PDO pour interagir avec une base
 * de données relationnelle. Cette classe fournit des méthodes pour rechercher,
 * créer, mettre à jour et supprimer des annonces immobilières, en utilisant des
 * requêtes SQL préparées pour garantir la sécurité et la performance des opérations
 * sur la base de données.
 */
final class PdoAnnonceRepository implements AnnonceRepository
{
    private const BASE_SELECT = <<<'SQL'
        SELECT  a.id                    AS annonce_id,
                a.titre                 AS titre,
                a.description           AS description,
                a.transaction           AS transaction,
                a.etat                  AS etat,
                a.date_publication      AS datePublication,
                a.derniere_modification AS derniereModification,
                a.prix_initial          AS prix_initial,
                a.prix_courant          AS prix_courant,
                a.loyer_initial         AS loyer_initial,
                a.loyer                 AS loyer,
                a.charges_initiales     AS charges_initiales,
                a.charges               AS charges,
                b.id                    AS bien_id,
                b.categorie             AS categorie,
                b.type                  AS type,
                b.ville                 AS ville,
                b.surface               AS surface,
                b.chambres              AS chambres,
                b.etage                 AS etage,
                b.terrain               AS terrain
        FROM    annonces a
        INNER JOIN biens b ON b.id = a.bien_id
        SQL;

    private readonly PDO $pdo;

    public function __construct(
        Database $database,
        private readonly AnnonceFactory $factory,
    ) {
        $this->pdo = $database->pdo();
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query(self::BASE_SELECT . ' ORDER BY a.id');

        return $this->factory->hydrateAll($stmt->fetchAll());
    }

    public function findById(int $id): ?Annonce
    {
        $stmt = $this->pdo->prepare(self::BASE_SELECT . ' WHERE a.id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);

        $row = $stmt->fetch();

        return $row === false ? null : $this->factory->hydrate($row);
    }

    public function findByFilters(array $filters): array
    {
        $where  = [];
        $params = [];

        $this->whereIn('b.categorie', 'cat', $filters['categorie'] ?? null, $where, $params);
        $this->whereIn('b.type', 'typ', $filters['type'] ?? null, $where, $params);
        $this->whereIn('a.transaction', 'trx', $filters['transaction'] ?? null, $where, $params);
        $this->whereIn('b.ville', 'ville', $filters['ville'] ?? null, $where, $params);
        $this->whereIn('a.etat', 'etat', $filters['etat'] ?? null, $where, $params);

        if (!empty($filters['prixMax'])) {
            $where[] = '((a.prix_courant IS NOT NULL AND a.prix_courant <= :prix_max)'
                . ' OR (a.loyer IS NOT NULL AND a.loyer <= :prix_max))';
            $params['prix_max'] = (string) $filters['prixMax'];
        }

        $sql = self::BASE_SELECT;
        if ($where !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY a.id';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $this->factory->hydrateAll($stmt->fetchAll());
    }

    /**
     * Ajoute une condition « colonne IN (...) » à partir d'une liste de valeurs.
     *
     * @param string[]|null $values
     * @param string[]      $where
     * @param array<string, string> $params
     */
    private function whereIn(string $column, string $prefix, ?array $values, array &$where, array &$params): void
    {
        $values = array_values($values ?? []);
        if ($values === []) {
            return;
        }

        $placeholders = [];
        foreach ($values as $i => $value) {
            $key                = $prefix . $i;
            $placeholders[]     = ':' . $key;
            $params[$key]       = (string) $value;
        }

        $where[] = $column . ' IN (' . implode(', ', $placeholders) . ')';
    }

    public function updatePrixCourant(int $id, string $nouveauPrix): void
    {
        $stmt = $this->pdo->prepare('UPDATE annonces SET prix_courant = :prix WHERE id = :id');
        $stmt->execute([
            'prix' => $nouveauPrix,
            'id'   => $id,
        ]);
    }

    public function create(array $data): int
    {
        $this->pdo->beginTransaction();

        try {
            $bienId = isset($data['bien_id']) && $data['bien_id'] !== null && $data['bien_id'] !== ''
                ? (string) $data['bien_id']
                : $this->generateBienId((string) $data['ville']);

            if (isset($data['bien_id']) && $data['bien_id'] !== null && $data['bien_id'] !== '') {
                if (!$this->bienExists($bienId) || $this->countByBienId($bienId) > 0) {
                    throw new RuntimeException("Bien indisponible pour une nouvelle annonce : {$bienId}");
                }
            } else {
                $this->insertBien($bienId, $data);
            }
            $this->insertAnnonce($bienId, $data);

            $id = (int) $this->pdo->lastInsertId();
            $this->pdo->commit();

            return $id;
        } catch (Throwable $e) {
            $this->rollBack();

            throw $e;
        }
    }

    public function update(int $id, array $data): void
    {
        $bienId = $this->findBienIdByAnnonceId($id);
        if ($bienId === null) {
            throw new RuntimeException("Annonce introuvable : {$id}");
        }

        $this->pdo->beginTransaction();

        try {
            $this->updateBien($bienId, $data);
            $this->updateAnnonce($id, $data);
            $this->pdo->commit();
        } catch (Throwable $e) {
            $this->rollBack();

            throw $e;
        }
    }

    public function delete(int $id): void
    {
        $bienId = $this->findBienIdByAnnonceId($id);
        if ($bienId === null) {
            return;
        }

        $stmt = $this->pdo->prepare('DELETE FROM annonces WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    private function insertBien(string $id, array $data): void
    {
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
    }

    private function updateBien(string $id, array $data): void
    {
        $stmt = $this->pdo->prepare(<<<'SQL'
            UPDATE biens
            SET categorie = :categorie,
                type = :type,
                ville = :ville,
                surface = :surface,
                chambres = :chambres,
                etage = :etage,
                terrain = :terrain
            WHERE id = :id
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
    }

    private function insertAnnonce(string $bienId, array $data): void
    {
        $stmt = $this->pdo->prepare(<<<'SQL'
            INSERT INTO annonces (
                bien_id,
                titre,
                description,
                transaction,
                etat,
                prix_initial,
                prix_courant,
                loyer_initial,
                loyer,
                charges_initiales,
                charges
            ) VALUES (
                :bien_id,
                :titre,
                :description,
                :transaction,
                :etat,
                :prix_initial,
                :prix_courant,
                :loyer_initial,
                :loyer,
                :charges_initiales,
                :charges
            )
            SQL);

        $stmt->execute([
            'bien_id'           => $bienId,
            'titre'             => $this->emptyToNull($data['titre'] ?? null),
            'description'       => $this->emptyToNull($data['description'] ?? null),
            'transaction'       => $data['transaction'],
            'etat'              => $data['etat'],
            'prix_initial'      => $data['prix_initial'],
            'prix_courant'      => $data['prix_courant'],
            'loyer_initial'     => $data['loyer_initial'] ?? null,
            'loyer'             => $data['loyer'],
            'charges_initiales' => $data['charges_initiales'] ?? null,
            'charges'           => $data['charges'],
        ]);
    }

    private function updateAnnonce(int $id, array $data): void
    {
        $stmt = $this->pdo->prepare(<<<'SQL'
            UPDATE annonces
            SET titre = :titre,
                description = :description,
                transaction = :transaction,
                etat = :etat,
                prix_initial = :prix_initial,
                prix_courant = :prix_courant,
                loyer_initial = :loyer_initial,
                loyer = :loyer,
                charges_initiales = :charges_initiales,
                charges = :charges
            WHERE id = :id
            SQL);

        $stmt->execute([
            'id'                => $id,
            'titre'             => $this->emptyToNull($data['titre'] ?? null),
            'description'       => $this->emptyToNull($data['description'] ?? null),
            'transaction'       => $data['transaction'],
            'etat'              => $data['etat'],
            'prix_initial'      => $data['prix_initial'],
            'prix_courant'      => $data['prix_courant'],
            'loyer_initial'     => $data['loyer_initial'] ?? null,
            'loyer'             => $data['loyer'],
            'charges_initiales' => $data['charges_initiales'] ?? null,
            'charges'           => $data['charges'],
        ]);
    }

    private function emptyToNull(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = (string) $value;

        return trim($value) === '' ? null : $value;
    }

    private function findBienIdByAnnonceId(int $id): ?string
    {
        $stmt = $this->pdo->prepare('SELECT bien_id FROM annonces WHERE id = :id');
        $stmt->execute(['id' => $id]);

        $bienId = $stmt->fetchColumn();

        return $bienId === false ? null : (string) $bienId;
    }

    private function countByBienId(string $bienId): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM annonces WHERE bien_id = :bien_id');
        $stmt->execute(['bien_id' => $bienId]);

        return (int) $stmt->fetchColumn();
    }

    private function generateBienId(string $ville): string
    {
        $base = strtolower(trim($ville));
        $base = preg_replace('/[^a-z0-9]+/', '-', $base) ?: 'bien';
        $base = trim($base, '-') ?: 'bien';

        do {
            $id = $base . '-' . bin2hex(random_bytes(4));
        } while ($this->bienExists($id));

        return $id;
    }

    private function bienExists(string $id): bool
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM biens WHERE id = :id');
        $stmt->execute(['id' => $id]);

        return (int) $stmt->fetchColumn() > 0;
    }

    private function rollBack(): void
    {
        if ($this->pdo->inTransaction()) {
            $this->pdo->rollBack();
        }
    }
}
