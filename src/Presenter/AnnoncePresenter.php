<?php

declare(strict_types=1);

namespace App\Presenter;

use App\Entity\Annonce\Annonce;
use App\Entity\Annonce\AnnonceLocation;
use App\Entity\Annonce\AnnonceVente;
use App\Entity\Bien\Appartement;
use App\Entity\Bien\BienImmo;
use App\Entity\Bien\Maison;
use App\Formatter\MoneyFormatter;

final class AnnoncePresenter
{
    public function __construct(
        private readonly MoneyFormatter $moneyFormatter,
    ) {
    }

    /**
     * @return array{titre: string, meta: string[], attributs: array<int, array{0: string, 1: string, 2?: string}>, transaction: string, etat: string}
     */
    public function presenter(Annonce $annonce): array
    {
        $bien = $annonce->getBien();

        return [
            'titre'       => sprintf('%s à %s', $this->libelleTypeBien($bien->getType()), $bien->getVille()),
            'meta'        => [
                $this->libelleTransaction($annonce->getTypeTransaction()),
                'Publié le ' . $annonce->getDatePublication()->format('d/m/Y'),
                'État : ' . $annonce->getEtat()->getLibelle(),
            ],
            'attributs'   => $this->presenterAttributs($annonce),
            'transaction' => $annonce->getTypeTransaction(),
            'etat'        => $annonce->getEtat()->value,
        ];
    }

    /**
     * @return array<int, array{0: string, 1: string, 2?: string}>
     */
    private function presenterAttributs(Annonce $annonce): array
    {
        $bien = $annonce->getBien();

        return [
            ['Surface',  sprintf('%.0f m²', $bien->getSurface())],
            ['Chambres', (string) $bien->getChambres()],
            ...$this->presenterAttributsBien($bien),
            ...$this->presenterAttributsAnnonce($annonce),
        ];
    }

    /**
     * @return array<int, array{0: string, 1: string}>
     */
    private function presenterAttributsBien(BienImmo $bien): array
    {
        if ($bien instanceof Appartement) {
            return [['Étage', (string) $bien->getEtage()]];
        }

        if ($bien instanceof Maison) {
            return [['Terrain', sprintf('%.0f m²', $bien->getTerrain())]];
        }

        return [];
    }

    /**
     * @return array<int, array{0: string, 1: string, 2?: string}>
     */
    private function presenterAttributsAnnonce(Annonce $annonce): array
    {
        if ($annonce instanceof AnnonceVente) {
            return $this->presenterAttributsVente($annonce);
        }

        if ($annonce instanceof AnnonceLocation) {
            return [
                ['Loyer',   $this->moneyFormatter->format($annonce->getLoyer()) . '/mois'],
                ['Charges', $this->moneyFormatter->format($annonce->getCharges()) . '/mois'],
                ['Total',   $this->moneyFormatter->format($annonce->getLoyerCharges()) . '/mois'],
            ];
        }

        return [];
    }

    /**
     * @return array<int, array{0: string, 1: string, 2?: string}>
     */
    private function presenterAttributsVente(AnnonceVente $annonce): array
    {
        $attributs = [];

        if ($annonce->aUneReduction()) {
            $prixInitial = $this->moneyFormatter->format($annonce->getPrixInitial());
            $prixCourant = $this->moneyFormatter->format($annonce->getPrixCourant());
            $pourcentage = $this->nettoyerPourcentage($annonce->getReductionPourcentage());

            $valeurTexte = "{$prixInitial} {$prixCourant} (-{$pourcentage}%)";
            $valeurHtml  = sprintf(
                '<s>%s</s> <strong>%s</strong> <em>(-%s%%)</em>',
                htmlspecialchars($prixInitial, ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($prixCourant, ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($pourcentage, ENT_QUOTES, 'UTF-8'),
            );

            $attributs[] = ['Prix', $valeurTexte, $valeurHtml];
        } else {
            $attributs[] = ['Prix', $this->moneyFormatter->format($annonce->getPrixCourant())];
        }

        $attributs[] = ['Prix au m²', $this->moneyFormatter->format($annonce->getPrixM2()) . '/m²'];

        return $attributs;
    }

    private function nettoyerPourcentage(string $pourcentage): string
    {
        return str_contains($pourcentage, '.') ? rtrim(rtrim($pourcentage, '0'), '.') : $pourcentage;
    }

    private function libelleTypeBien(string $type): string
    {
        return match ($type) {
            'appartement' => 'Appartement',
            'maison'      => 'Maison',
            default       => ucfirst($type),
        };
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
