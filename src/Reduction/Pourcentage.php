<?php

declare(strict_types=1);

namespace App\Reduction;

use InvalidArgumentException;

final class Pourcentage
{
    public function __construct(private readonly string $pourcent)
    {
        if (bccomp($pourcent, '0', 4) <= 0 || bccomp($pourcent, '100', 4) > 0) {
            throw new InvalidArgumentException(
                "Le pourcentage de réduction doit être strictement positif et <= 100, reçu : {$pourcent}"
            );
        }
    }

    public function appliquer(string $prixInitial): string
    {
        $taux    = bcdiv($this->pourcent, '100', 4);
        $facteur = bcsub('1', $taux, 4);

        return bcmul($prixInitial, $facteur, 2);
    }

    public function libelle(): string
    {
        return "-{$this->pourcent}%";
    }

    public function valeur(): string
    {
        return $this->pourcent;
    }
}
