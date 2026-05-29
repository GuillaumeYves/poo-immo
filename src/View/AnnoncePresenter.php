<?php

declare(strict_types=1);

namespace App\View;

use App\Model\Annonce\Annonce;
use App\Model\Annonce\PrixVariation;
use App\Model\Formatter\MoneyFormatter;

/**
 * Transforme une annonce en un tableau de données prêt à être utilisé dans une vue.
 */
final class AnnoncePresenter
{
    public function __construct(
        private readonly MoneyFormatter $moneyFormatter,
    ) {
    }

    public function presenter(Annonce $annonce): array
    {
        $bien = $annonce->getBien();
        $categorie = $bien->getCategorie();
        $type = $bien->getType();

        $publication = $annonce->getDatePublication();
        $modification = $annonce->getDerniereModification();
        $publicationFmt = $publication->format('d/m/Y');
        $modificationFmt = $modification->format('d/m/Y');
        $aEteModifiee = $modification > $publication
            && $publication->format('Y-m-d') !== $modification->format('Y-m-d');

        $ligneDates = $aEteModifiee
            ? sprintf('Publié le %s · Modifié le %s', $publicationFmt, $modificationFmt)
            : 'Publié le ' . $publicationFmt;

        $titreFallback = sprintf('%s à %s', $categorie->libelle(), $bien->getVille());

        return [
            'id'                   => $annonce->getId(),
            'categorie'            => $categorie->value,
            'categorieLibelle'     => $categorie->libelle(),
            'type'                 => $type?->value,
            'typeLibelle'          => $type?->libelle(),
            'ville'                => $bien->getVille(),
            'titre'                => $annonce->getTitre() ?? $titreFallback,
            'description'          => $annonce->getDescription(),
            'meta'                 => [
                $this->libelleTransaction($annonce->getTypeTransaction()),
                $ligneDates,
            ],
            'datePublication'      => $publicationFmt,
            'derniereModification' => $aEteModifiee ? $modificationFmt : null,
            'attributs'            => $this->presenterAttributs($annonce),
            'transaction'          => $annonce->getTypeTransaction(),
            'transactionLibelle'   => $this->libelleTransaction($annonce->getTypeTransaction()),
            'etat'                 => $annonce->getEtat()->value,
            'etatLibelle'          => $annonce->getEtat()->getLibelle(),
            'prix'                 => $this->presenterPrix($annonce->getPrixVariation()),
        ];
    }

    private function presenterPrix(PrixVariation $variation): array
    {
        $courantFmt = $this->moneyFormatter->format($variation->courantBrut) . $variation->suffixe;

        if (!$variation->aChange()) {
            return [
                'prixCourant' => $courantFmt,
                'prixInitial' => null,
                'sens'        => PrixVariation::SENS_AUCUNE,
                'pourcentage' => '0',
                'tri'         => $variation->courantBrut,
            ];
        }

        return [
            'prixCourant' => $courantFmt,
            'prixInitial' => $this->moneyFormatter->format($variation->initialBrut) . $variation->suffixe,
            'sens'        => $variation->sens(),
            'pourcentage' => $this->nettoyerPourcentage($variation->pourcentageAbsolu()),
            'tri'         => $variation->courantBrut,
        ];
    }

    private function presenterAttributs(Annonce $annonce): array
    {
        $bien = $annonce->getBien();

        return [
            ['Surface',  sprintf('%.0f m²', $bien->getSurface())],
            ['Chambres', (string) $bien->getChambres()],
            ...$bien->getAttributsSpecifiques(),
            ...$annonce->getAttributsSpecifiques($this->moneyFormatter),
        ];
    }

    private function nettoyerPourcentage(string $pourcentage): string
    {
        return str_contains($pourcentage, '.') ? rtrim(rtrim($pourcentage, '0'), '.') : $pourcentage;
    }

    private function libelleTransaction(string $transaction): string
    {
        return match ($transaction) {
            'vente'    => 'Vente',
            'location' => 'Location',
            default    => ucfirst($transaction),
        };
    }
}
