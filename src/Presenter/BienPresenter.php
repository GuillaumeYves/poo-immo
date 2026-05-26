<?php

declare(strict_types=1);

require_once __DIR__ . '/../Entity/Annonce.php';
require_once __DIR__ . '/../Entity/AnnonceVente.php';
require_once __DIR__ . '/../Entity/AnnonceLocation.php';
require_once __DIR__ . '/../Entity/Appartement.php';
require_once __DIR__ . '/../Entity/Maison.php';

class BienPresenter
{
    /**
     * @param Annonce[] $annonces
     * @return array{entete: string, annonces: array<int, array{titre: string, meta: string[], attributs: array<int, array{0: string, 1: string}>, transaction: string, etat: string}>}
     */
    public function presenterCatalogue(array $annonces, string $titre = 'Catalogue'): array
    {
        $n       = count($annonces);
        $pluriel = $n > 1 ? 's' : '';

        return [
            'entete'   => sprintf('%s : %d annonce%s', $titre, $n, $pluriel),
            'annonces' => array_map(fn(Annonce $a) => $this->presenterAnnonce($a), $annonces),
        ];
    }

    /**
     * @return array{titre: string, meta: string[], attributs: array<int, array{0: string, 1: string}>, transaction: string, etat: string}
     */
    public function presenterAnnonce(Annonce $annonce): array
    {
        $bien = $annonce->getBien();

        return [
            'titre'       => sprintf('%s à %s', $bien->getType(), $bien->getVille()),
            'meta'        => [
                $annonce->getTypeTransaction(),
                'Publié le ' . $annonce->getDatePublication()->format('d/m/Y'),
                'État : ' . $annonce->getEtat()->getLibelle(),
            ],
            'attributs'   => $this->buildAttributs($annonce),
            'transaction' => $annonce->getTypeTransaction(),
            'etat'        => $annonce->getEtat()->value,
        ];
    }

    /**
     * @return array<int, array{0: string, 1: string}>
     */
    private function buildAttributs(Annonce $annonce): array
    {
        $bien = $annonce->getBien();

        $rows = [
            ['Surface',  sprintf('%.0f m²', $bien->getSurface())],
            ['Chambres', (string) $bien->getChambres()],
        ];

        if ($bien instanceof Appartement) {
            $rows[] = ['Étage', (string) $bien->getEtage()];
        } elseif ($bien instanceof Maison) {
            $rows[] = ['Terrain', sprintf('%.0f m²', $bien->getTerrain())];
        }

        if ($annonce instanceof AnnonceVente) {
            $rows[] = ['Prix',       $this->formatEuros($annonce->getPrix())];
            $rows[] = ['Prix au m²', $this->formatEuros($annonce->getPrixM2()) . '/m²'];
        } elseif ($annonce instanceof AnnonceLocation) {
            $rows[] = ['Loyer',   $this->formatEuros($annonce->getLoyer())        . '/mois'];
            $rows[] = ['Charges', $this->formatEuros($annonce->getCharges())      . '/mois'];
            $rows[] = ['Total',   $this->formatEuros($annonce->getLoyerCharges()) . '/mois'];
        }

        return $rows;
    }

    private function formatEuros(int|float $montant): string
    {
        return number_format((float) $montant, 0, ',', ' ') . ' €';
    }
}
