<?php

declare(strict_types=1);

namespace App\Presenter;

use App\Entity\Annonce\Annonce;

final class CataloguePresenter
{
    public function __construct(
        private readonly AnnoncePresenter $annoncePresenter,
    ) {
    }

    /**
     * @param Annonce[] $annonces
     * @return array{entete: string, annonces: array<int, array{titre: string, meta: string[], attributs: array<int, array{0: string, 1: string, 2?: string}>, transaction: string, etat: string}>}
     */
    public function presenter(array $annonces, string $titre = 'Catalogue'): array
    {
        $n       = count($annonces);
        $pluriel = $n > 1 ? 's' : '';

        return [
            'entete'   => sprintf('%s : %d annonce%s', $titre, $n, $pluriel),
            'annonces' => array_map(fn(Annonce $a) => $this->annoncePresenter->presenter($a), $annonces),
        ];
    }
}
