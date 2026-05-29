<?php

declare(strict_types=1);

namespace App\View;

use App\Model\Annonce\Annonce;

/**
 * Transforme une liste d'annonces en un tableau de données prêt à être utilisé dans une vue.
 */
final class CataloguePresenter
{
    public function __construct(
        private readonly AnnoncePresenter $annoncePresenter,
    ) {
    }

    public function presenter(array $annonces): array
    {
        return [
            'annonces' => array_map(fn(Annonce $a) => $this->annoncePresenter->presenter($a), $annonces),
        ];
    }
}
