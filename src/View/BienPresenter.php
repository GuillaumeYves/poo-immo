<?php

declare(strict_types=1);

namespace App\View;

use App\Model\Bien\Bien;

/**
 * Transforme un bien en un tableau de données prêt à être utilisé dans une vue.
 */
final class BienPresenter
{
    public function presenter(Bien $bien): array
    {
        $categorie = $bien->getCategorie();
        $type = $bien->getType();

        return [
            'id'               => $bien->getId(),
            'categorie'        => $categorie->value,
            'categorieLibelle' => $categorie->libelle(),
            'type'             => $type?->value,
            'typeLibelle'      => $type?->libelle(),
            'ville'            => $bien->getVille(),
            'titre'            => sprintf('%s à %s', $categorie->libelle(), $bien->getVille()),
            'attributs'        => [
                ['Surface',  sprintf('%.0f m²', $bien->getSurface())],
                ['Chambres', (string) $bien->getChambres()],
                ...$bien->getAttributsSpecifiques(),
            ],
        ];
    }
}
