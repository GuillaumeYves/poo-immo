<?php

declare(strict_types=1);

namespace App\Model\Service;

use App\Model\Annonce\AnnonceRepository;
use App\Model\Annonce\AnnonceVente;
use App\Model\Observer\PrixObserver;
use RuntimeException;

/* *
 * Service pour la gestion des modifications de prix des annonces de vente immobilière,
 * permettant de notifier les observateurs enregistrés lorsqu'un changement de prix se
 * produit sur une annonce de vente. Ce service peut être utilisé pour centraliser la
 * logique de gestion des prix des annonces de vente et faciliter la notification des
 * différentes parties intéressées lorsque des changements de prix surviennent.
 */
final class PrixService
{
    private array $observers = [];

    public function __construct(
        private readonly AnnonceRepository $repository,
    ) {
    }

    public function subscribe(PrixObserver $observer): void
    {
        $this->observers[] = $observer;
    }

    public function modifierPrix(int $annonceId, string $nouveauPrix): void
    {
        if (!preg_match('/^\d+(?:\.\d{1,2})?$/', $nouveauPrix) || bccomp($nouveauPrix, '0', 2) <= 0) {
            throw new RuntimeException("Prix invalide : {$nouveauPrix}");
        }

        $annonce    = $this->chargerVente($annonceId);
        $ancienPrix = $annonce->getPrixCourant();

        if (bccomp($ancienPrix, $nouveauPrix, 2) === 0) {
            return;
        }

        $this->repository->updatePrixCourant($annonceId, $nouveauPrix);

        foreach ($this->observers as $observer) {
            $observer->onPrixModifie($annonce, $ancienPrix, $nouveauPrix);
        }
    }

    private function chargerVente(int $annonceId): AnnonceVente
    {
        $annonce = $this->repository->findById($annonceId)
            ?? throw new RuntimeException("Annonce introuvable : id={$annonceId}");

        if (!$annonce instanceof AnnonceVente) {
            throw new RuntimeException(
                "Les modifications de prix ne s'appliquent qu'aux annonces de vente (id={$annonceId} est une "
                . $annonce->getTypeTransaction() . ')'
            );
        }

        return $annonce;
    }
}
