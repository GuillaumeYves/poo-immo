<?php

declare(strict_types=1);

namespace App\Database;

use PDO;
use PDOException;
use RuntimeException;


final class Database
{
    private static ?self $instance = null;

    private readonly PDO $pdo;

    private function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public static function getInstance(): self
    {
        return self::$instance ??= new self(self::connect(withDbName: true));
    }

    public static function bootstrap(): self
    {
        self::$instance = new self(self::connect(withDbName: false));

        return self::$instance;
    }

    public function pdo(): PDO
    {
        return $this->pdo;
    }

    /**
     * Exécute un script SQL qui peut contenir plusieurs statements
     * (DROP / CREATE / INSERT...). Utilisé par le runner db.php.
     */
    public function execScript(string $sql): void
    {
        try {
            $this->pdo->exec($sql);
        } catch (PDOException $e) {
            throw new RuntimeException('Échec de l\'exécution du script SQL : ' . $e->getMessage(), previous: $e);
        }
    }

    private static function connect(bool $withDbName): PDO
    {
        $config = require dirname(__DIR__, 2) . '/config/database.php';

        $dsnParts = [
            'host=' . $config['host'],
            'port=' . $config['port'],
            'charset=' . $config['charset'],
        ];
        if ($withDbName) {
            $dsnParts[] = 'dbname=' . $config['dbname'];
        }
        $dsn = 'mysql:' . implode(';', $dsnParts);

        try {
            return new PDO($dsn, $config['username'], $config['password'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            throw new RuntimeException('Connexion MySQL impossible : ' . $e->getMessage(), previous: $e);
        }
    }
}
