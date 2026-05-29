<?php

declare(strict_types=1);

namespace App\Model\Form;

/* *
 * Résultat de la validation d'un formulaire, contenant les données validées, les
 * données brutes du formulaire et les erreurs de validation éventuelles. Cette
 * classe permet de centraliser les résultats de la validation et de fournir une
 * interface simple pour vérifier la présence d'erreurs et accéder aux données
 * validées ou brutes du formulaire.
 */
final class ValidationResult
{
    public function __construct(
        public readonly array $data,
        public readonly array $formData,
        public readonly array $errors,
    ) {
    }

    public function hasErrors(): bool
    {
        return $this->errors !== [];
    }
}
