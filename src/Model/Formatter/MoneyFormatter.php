<?php

declare(strict_types=1);

namespace App\Model\Formatter;

/* *
 * Formatter pour les montants d'argent, permettant de formater les valeurs numériques
 * en chaînes de caractères avec un format monétaire, en ajoutant des séparateurs
 * de milliers et le symbole de l'euro. Ce formatter est utilisé dans les mappers
 * et les exportateurs pour afficher les prix des biens immobiliers de manière
 * lisible et cohérente dans l'application.
 */
final class MoneyFormatter
{
    public function format(string $montant): string
    {
        return number_format((float) $montant, 0, ',', ' ') . ' €';
    }
}
