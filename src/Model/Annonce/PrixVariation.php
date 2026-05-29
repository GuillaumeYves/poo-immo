<?php

declare(strict_types=1);

namespace App\Model\Annonce;

/* *
 * Représente la variation de prix d'une annonce de vente immobilière, en comparant
 * le prix courant au prix initial, et en fournissant des méthodes pour déterminer
 * le sens de la variation (réduction, hausse ou aucune) et le pourcentage de variation.
 */
final class PrixVariation
{
    public const SENS_REDUCTION = 'reduction';
    public const SENS_HAUSSE    = 'hausse';
    public const SENS_AUCUNE    = 'aucune';

    public function __construct(
        public readonly string $courantBrut,
        public readonly string $initialBrut,
        public readonly string $suffixe = '',
    ) {
    }

    public function aChange(): bool
    {
        return bccomp($this->courantBrut, $this->initialBrut, 2) !== 0;
    }

    public function aUneReduction(): bool
    {
        return bccomp($this->courantBrut, $this->initialBrut, 2) < 0;
    }

    public function aUneHausse(): bool
    {
        return bccomp($this->courantBrut, $this->initialBrut, 2) > 0;
    }

    public function sens(): string
    {
        if ($this->aUneReduction()) {
            return self::SENS_REDUCTION;
        }
        if ($this->aUneHausse()) {
            return self::SENS_HAUSSE;
        }
        return self::SENS_AUCUNE;
    }

    public function pourcentageAbsolu(): string
    {
        if (!$this->aChange()) {
            return '0';
        }

        $delta = bcsub($this->courantBrut, $this->initialBrut, 6);
        $taux  = bcdiv($delta, $this->initialBrut, 6);
        $pct   = bcmul($taux, '100', 2);

        return ltrim($pct, '-');
    }
}
