<?php

declare(strict_types=1);

namespace App\Model\Form;

use App\Model\Annonce\EtatAnnonce;

/* *
 * Validateur pour les formulaires d'annonces immobilières, permettant de valider
 * les données soumises par l'utilisateur, en gérant les différentes propriétés des
 * annonces et de leurs biens associés en fonction du type de bien et du type
 * d'annonce (vente ou location). Ce validateur utilise un DecimalParser pour
 * valider et normaliser les valeurs numériques, ainsi qu'un BienFormValidator
 * pour valider les données liées au bien associé à l'annonce.
 */
final class AnnonceFormValidator
{
    public function __construct(
        private readonly DecimalParser $decimals,
        private readonly BienFormValidator $bienValidator,
    ) {
    }

    public function defaultFormData(): array
    {
        return [
            ...$this->bienValidator->defaultFormData(),
            'bien_mode'         => 'new',
            'bien_id'           => '',
            'titre'             => '',
            'description'       => '',
            'transaction'       => 'vente',
            'etat'              => EtatAnnonce::Disponible->value,
            'prix_initial'      => '',
            'prix_courant'      => '',
            'loyer_initial'     => '',
            'loyer'             => '',
            'charges_initiales' => '',
            'charges'           => '0',
        ];
    }

    /**
     * @param string[] $attachableBienIds
     */
    public function validate(array $input, bool $allowExistingBien = false, array $attachableBienIds = []): ValidationResult
    {
        $formData = $this->normalise($input);
        $data     = $formData;
        $errors   = [];

        if ($this->usesExistingBien($formData, $allowExistingBien)) {
            $this->validateExistingBien($formData, $data, $errors, $attachableBienIds);
        } else {
            $formData['bien_mode'] = 'new';
            $data['bien_mode'] = 'new';
            $data['bien_id'] = null;
            $this->validateNewBien($formData, $data, $errors);
        }

        $this->validateTransaction($formData, $errors);
        $this->validateEtat($formData, $errors);

        if ($formData['transaction'] === 'vente') {
            $this->validatePrixVente($formData, $data, $errors);
        }
        if ($formData['transaction'] === 'location') {
            $this->validatePrixLocation($formData, $data, $errors);
        }

        return new ValidationResult($data, $formData, $errors);
    }

    private function normalise(array $input): array
    {
        $data = $this->defaultFormData();

        foreach ($data as $key => $default) {
            $value = $input[$key] ?? $default;
            $data[$key] = is_scalar($value) ? trim((string) $value) : $default;
        }

        $data['categorie']   = strtolower($data['categorie']);
        $data['transaction'] = strtolower($data['transaction']);
        $data['etat']        = strtolower($data['etat']);
        $data['bien_mode']   = strtolower($data['bien_mode']);

        return $data;
    }

    private function usesExistingBien(array $formData, bool $allowExistingBien): bool
    {
        return $allowExistingBien && $formData['bien_mode'] === 'existing';
    }

    private function validateNewBien(array &$formData, array &$data, array &$errors): void
    {
        $result = $this->bienValidator->validate($formData);

        $formData = [
            ...$formData,
            ...$result->formData,
        ];
        $data = [
            ...$data,
            ...$result->data,
        ];
        $errors = [
            ...$errors,
            ...$result->errors,
        ];
    }

    /**
     * @param string[] $attachableBienIds
     */
    private function validateExistingBien(array $formData, array &$data, array &$errors, array $attachableBienIds): void
    {
        if ($formData['bien_id'] === '') {
            $errors['bien_id'] = 'Choisissez un bien disponible.';
            return;
        }

        if (!in_array($formData['bien_id'], $attachableBienIds, true)) {
            $errors['bien_id'] = "Ce bien n'est pas disponible pour une annonce.";
            return;
        }

        $data['bien_mode'] = 'existing';
        $data['bien_id'] = $formData['bien_id'];
    }

    private function validateTransaction(array $formData, array &$errors): void
    {
        if (!in_array($formData['transaction'], ['vente', 'location'], true)) {
            $errors['transaction'] = 'Transaction invalide.';
        }
    }

    private function validateEtat(array $formData, array &$errors): void
    {
        $valides = array_map(static fn(EtatAnnonce $e): string => $e->value, EtatAnnonce::cases());
        if (!in_array($formData['etat'], $valides, true)) {
            $errors['etat'] = 'Etat invalide.';
        }
    }

    private function validatePrixVente(array $formData, array &$data, array &$errors): void
    {
        $prixInitial = $this->decimals->parse($formData['prix_initial'], 'Prix initial', true, false, $errors, 'prix_initial');

        $courantRaw = $formData['prix_courant'];
        $prixCourant = $courantRaw === ''
            ? $prixInitial
            : $this->decimals->parse($courantRaw, 'Prix actuel', true, false, $errors, 'prix_courant');

        $data['prix_initial']      = $prixInitial;
        $data['prix_courant']      = $prixCourant;
        $data['loyer_initial']     = null;
        $data['loyer']             = null;
        $data['charges_initiales'] = null;
        $data['charges']           = null;
    }

    private function validatePrixLocation(array $formData, array &$data, array &$errors): void
    {
        $loyer   = $this->decimals->parse($formData['loyer'], 'Loyer', true, false, $errors, 'loyer');
        $charges = $this->decimals->parse($formData['charges'], 'Charges', false, true, $errors, 'charges') ?? '0.00';

        $loyerInitial = $formData['loyer_initial'] === ''
            ? $loyer
            : $this->decimals->parse($formData['loyer_initial'], 'Loyer initial', true, false, $errors, 'loyer_initial');

        $chargesInitiales = $formData['charges_initiales'] === ''
            ? $charges
            : ($this->decimals->parse($formData['charges_initiales'], 'Charges initiales', false, true, $errors, 'charges_initiales') ?? '0.00');

        $data['loyer_initial']     = $loyerInitial;
        $data['loyer']             = $loyer;
        $data['charges_initiales'] = $chargesInitiales;
        $data['charges']           = $charges;
        $data['prix_initial']      = null;
        $data['prix_courant']      = null;
    }
}
