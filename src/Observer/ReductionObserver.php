<?php

declare(strict_types=1);

namespace App\Observer;

use App\Entity\Annonce\AnnonceVente;
use App\Reduction\Pourcentage;


interface ReductionObserver
{
    public function onReductionAppliquee(
        AnnonceVente $annonce,
        string $ancienPrix,
        string $nouveauPrix,
        Pourcentage $reduction,
    ): void;
}
