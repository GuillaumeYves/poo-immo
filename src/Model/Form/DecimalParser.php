<?php

declare(strict_types=1);

namespace App\Model\Form;

/* *
 * Parser pour les valeurs décimales dans les formulaires, permettant de valider
 * et de normaliser les entrées numériques, en gérant les différentes options
 * telles que l'obligation du champ, l'autorisation des valeurs nulles ou zéro,
 * et en fournissant des messages d'erreur appropriés en cas de validation
 * échouée. Ce parser est utilisé dans les validateurs de formulaires pour les
 * annonces et les biens immobiliers afin d'assurer la cohérence des données
 * numériques dans l'application.
 */
final class DecimalParser
{
    public function parse(
        string $value,
        string $label,
        bool $required,
        bool $allowZero,
        array &$errors,
        string $field,
    ): ?string {
        $value = str_replace(',', '.', $value);

        if ($value === '') {
            if ($required) {
                $errors[$field] = "{$label} obligatoire.";
            }
            return null;
        }

        if (!preg_match('/^\d+(?:\.\d{1,2})?$/', $value)) {
            $errors[$field] = "{$label} doit etre un nombre positif avec deux decimales maximum.";
            return null;
        }

        $normalised = $this->format($value);
        $comparison = bccomp($normalised, '0.00', 2);

        if ((!$allowZero && $comparison <= 0) || ($allowZero && $comparison < 0)) {
            $errors[$field] = $allowZero
                ? "{$label} ne peut pas etre negatif."
                : "{$label} doit etre strictement positif.";
            return null;
        }

        return $normalised;
    }

    public function format(string $value): string
    {
        $value = str_replace(',', '.', $value);
        [$integer, $decimal] = array_pad(explode('.', $value, 2), 2, '');
        $integer = ltrim($integer, '0');
        $integer = $integer === '' ? '0' : $integer;
        $decimal = substr(str_pad($decimal, 2, '0'), 0, 2);

        return $integer . '.' . $decimal;
    }
}
