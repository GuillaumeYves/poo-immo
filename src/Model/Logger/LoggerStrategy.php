<?php

declare(strict_types=1);

namespace App\Model\Logger;

/* *
 * Interface pour les stratégies de logging, définissant la méthode log pour
 * enregistrer les messages de log. Cette interface permet de définir différentes
 * implémentations de loggers (fichier, base de données, etc.) qui peuvent être
 * utilisées de manière interchangeable dans l'application en fonction des besoins
 * et des préférences de l'utilisateur ou du développeur.
 */
interface LoggerStrategy
{
    public function log(string $message): void;
}
