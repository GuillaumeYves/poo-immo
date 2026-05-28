<?php

declare(strict_types=1);

namespace App\Database;

use App\Entity\Annonce\Annonce;
use App\Entity\Annonce\AnnonceFactory;
use App\Entity\Annonce\AnnonceRepositoryInterface;
use PDO;
use RuntimeException;

final class AnnonceRepository implements AnnonceRepositoryInterface
{
    private const BASE_SELECT = <<<'SQL'
        SELECT  a.id               AS annonce_id,
                a.transaction      AS transaction,
                a.etat             AS etat,
                a.date_publication AS datePublication,
                a.prix             AS prix,
                a.loyer            AS loyer,
                a.charges          AS charges,
                b.id               AS bien_id,
                b.type             AS type,
                b.ville            AS ville,
                b.surface          AS surface,
                b.chambres         AS chambres,
                b.description      AS description,
                b.etage            AS etage,
                b.terrain          AS terrain
        FROM    annonces a
        INNER JOIN biens b ON b.id = a.bien_id
        SQL;

    private readonly PDO $pdo;
    private readonly AnnonceFactory $factory;

    public function __construct(?Database $database = null, ?AnnonceFactory $factory = null)
    {
        $this->pdo     = ($database ?? Database::getInstance())->pdo();
        $this->factory = $factory ?? new AnnonceFactory();
    }

    public function add(Annonce $annonce): void
    {
        throw new RuntimeException(
            'AnnonceRepository::add() non implémenté : '
            . 'la persistance d\'ajout sort du périmètre actuel (il faudrait notamment un BienRepository et un id sur BienImmo).'
        );
    }

    /** @return Annonce[] */
    public function findAll(): array
    {
        $stmt = $this->pdo->query(self::BASE_SELECT . ' ORDER BY a.id');

        return $this->factory->hydrateAll($stmt->fetchAll());
    }

    public function findOneByVille(string $ville): ?Annonce
    {
        $stmt = $this->pdo->prepare(self::BASE_SELECT . ' WHERE b.ville = :ville LIMIT 1');
        $stmt->execute(['ville' => $ville]);

        $row = $stmt->fetch();

        return $row === false ? null : $this->factory->hydrate($row);
    }

    /** @return Annonce[] */
    public function findByVille(string $ville): array
    {
        $stmt = $this->pdo->prepare(self::BASE_SELECT . ' WHERE b.ville = :ville ORDER BY a.id');
        $stmt->execute(['ville' => $ville]);

        return $this->factory->hydrateAll($stmt->fetchAll());
    }

    /** @return Annonce[] */
    public function findByTransaction(string $type): array
    {
        $stmt = $this->pdo->prepare(self::BASE_SELECT . ' WHERE a.transaction = :transaction ORDER BY a.id');
        $stmt->execute(['transaction' => strtolower($type)]);

        return $this->factory->hydrateAll($stmt->fetchAll());
    }

    /** @return Annonce[] */
    public function findByTypeBien(string $type): array
    {
        $stmt = $this->pdo->prepare(self::BASE_SELECT . ' WHERE b.type = :type ORDER BY a.id');
        $stmt->execute(['type' => strtolower($type)]);

        return $this->factory->hydrateAll($stmt->fetchAll());
    }

    public function count(): int
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM annonces')->fetchColumn();
    }
}
