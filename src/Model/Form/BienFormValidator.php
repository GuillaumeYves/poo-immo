<?php

declare(strict_types=1);

namespace App\Model\Form;

use App\Model\Bien\CategorieBien;
use App\Model\Bien\TypeBien;
use App\Model\Bien\Ville;

/* *
 * Validateur pour les formulaires de biens immobiliers, permettant de valider
 * les données soumises par l'utilisateur, en gérant les différentes propriétés des
 * biens en fonction de leur type (appartement, maison, villa). Ce validateur utilise un
 * DecimalParser pour valider et normaliser les valeurs numériques.
 */
final class BienFormValidator
{
    public function __construct(
        private readonly DecimalParser $decimals,
    ) {
    }

    public function defaultFormData(): array
    {
        return [
            'categorie' => CategorieBien::Appartement->value,
            'type'      => '',
            'ville'     => '',
            'surface'   => '',
            'chambres'  => '',
            'etage'     => '0',
            'terrain'   => '',
        ];
    }

    public function validate(array $input): ValidationResult
    {
        $formData = $this->normalise($input);
        $data     = $formData;
        $errors   = [];

        $this->validateCategorie($formData, $data, $errors);
        $this->validateType($formData, $data, $errors);
        $this->validateVille($formData, $errors);
        $this->validateSurface($formData, $data, $errors);
        $this->validateChambres($formData, $data, $errors);
        $this->validateEtageOuTerrain($formData, $data, $errors);

        return new ValidationResult($data, $formData, $errors);
    }

    private function normalise(array $input): array
    {
        $data = $this->defaultFormData();

        foreach ($data as $key => $default) {
            $value = $input[$key] ?? $default;
            $data[$key] = is_scalar($value) ? trim((string) $value) : $default;
        }

        $data['categorie'] = strtolower($data['categorie']);

        return $data;
    }

    private function validateCategorie(array $formData, array &$data, array &$errors): void
    {
        $categorie = CategorieBien::tryFrom($formData['categorie']);
        if ($categorie === null) {
            $errors['categorie'] = 'Catégorie de bien invalide.';
            return;
        }

        $data['categorie'] = $categorie->value;
    }

    private function validateType(array $formData, array &$data, array &$errors): void
    {
        if ($formData['type'] === '') {
            $data['type'] = null;
            return;
        }

        if (TypeBien::tryFrom($formData['type']) === null) {
            $errors['type'] = 'Type invalide (Studio, T1, T2...).';
            return;
        }

        $data['type'] = $formData['type'];
    }

    private function validateVille(array $formData, array &$errors): void
    {
        if ($formData['ville'] === '') {
            $errors['ville'] = 'La ville est obligatoire.';
            return;
        }

        if (Ville::tryFrom($formData['ville']) === null) {
            $errors['ville'] = 'Ville invalide, choisissez une ville dans la liste.';
        }
    }

    private function validateSurface(array $formData, array &$data, array &$errors): void
    {
        $surface = $this->decimals->parse($formData['surface'], 'Surface', true, false, $errors, 'surface');
        if ($surface !== null) {
            $data['surface'] = $surface;
        }
    }

    private function validateChambres(array $formData, array &$data, array &$errors): void
    {
        if (!ctype_digit($formData['chambres'])) {
            $errors['chambres'] = 'Le nombre de chambres doit etre un entier positif.';
            return;
        }

        $data['chambres'] = (int) $formData['chambres'];
    }

    private function validateEtageOuTerrain(array $formData, array &$data, array &$errors): void
    {
        if ($formData['categorie'] === CategorieBien::Appartement->value) {
            if ($formData['etage'] === '') {
                $data['etage'] = 0;
            } elseif (!ctype_digit($formData['etage'])) {
                $errors['etage'] = "L'etage doit etre un entier positif.";
            } else {
                $data['etage'] = (int) $formData['etage'];
            }

            $data['terrain'] = null;
            return;
        }

        if ($formData['categorie'] === CategorieBien::Maison->value || $formData['categorie'] === CategorieBien::Villa->value) {
            $terrain = $this->decimals->parse($formData['terrain'], 'Terrain', true, false, $errors, 'terrain');
            if ($terrain !== null) {
                $data['terrain'] = $terrain;
            }

            $data['etage'] = null;
        }
    }
}
