<?php

declare(strict_types=1);

namespace App\Model\Logger;

use RuntimeException;

/* *
 * Logger pour les messages d'erreur et d'information, utilisant une stratégie de
 * logging fournie pour enregistrer les messages dans un fichier ou une autre
 * destination. Ce logger peut être utilisé dans toute l'application pour centraliser
 * la gestion des logs et faciliter le suivi des événements importants ou des erreurs
 * qui se produisent lors de l'exécution de l'application.
 */
final class FileLogger implements LoggerStrategy
{
    public function __construct(private readonly string $cheminFichier)
    {
        $dossier = dirname($cheminFichier);
        if (!is_dir($dossier) && !mkdir($dossier, 0755, true) && !is_dir($dossier)) {
            throw new RuntimeException("Impossible de créer le dossier de log : {$dossier}");
        }
    }

    public function log(string $message): void
    {
        $ligne = rtrim($message, "\r\n") . "\n";
        if (file_put_contents($this->cheminFichier, $ligne, FILE_APPEND | LOCK_EX) === false) {
            throw new RuntimeException("Échec de l'écriture du log : {$this->cheminFichier}");
        }
    }
}
