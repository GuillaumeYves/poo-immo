<?php

declare(strict_types=1);

namespace App\Presenter;

use App\Entity\Annonce;
use App\Formatter\MoneyFormatter;

final class AnnoncePresenter
{
    public function __construct(
        private readonly MoneyFormatter $moneyFormatter,
    ) {
    }

    /**
     * @return array{titre: string, meta: string[], attributs: array<int, array{0: string, 1: string}>, transaction: string, etat: string}
     */
    public function presenter(Annonce $annonce): array
    {
        $bien = $annonce->getBien();

        return [
            'titre'       => sprintf('%s à %s', $bien->getType(), $bien->getVille()),
            'meta'        => [
                $annonce->getTypeTransaction(),
                'Publié le ' . $annonce->getDatePublication()->format('d/m/Y'),
                'État : ' . $annonce->getEtat()->getLibelle(),
            ],
            'attributs'   => [
                ['Surface',  sprintf('%.0f m²', $bien->getSurface())],
                ['Chambres', (string) $bien->getChambres()],
                ...$bien->getAttributsAffichage(),
                ...$annonce->getAttributsAffichage($this->moneyFormatter),
            ],
            'transaction' => $annonce->getTypeTransaction(),
            'etat'        => $annonce->getEtat()->value,
        ];
    }
}
