<?php

declare(strict_types=1);

namespace App\Model\Service;

use App\Model\Annonce\AnnonceLocation;
use App\Model\Observer\LoyerObserver;

/* *
 * Service pour la gestion des changements de loyer des annonces de location immobilière,
 * permettant de notifier les observateurs enregistrés lorsqu'un changement de loyer se
 * produit sur une annonce de location. Ce service peut être utilisé pour centraliser la
 * logique de gestion des loyers des annonces de location et faciliter la notification des
 * différentes parties intéressées lorsque des changements de loyer surviennent.
 */
final class LoyerService
{
    private array $observers = [];

    public function subscribe(LoyerObserver $observer): void
    {
        $this->observers[] = $observer;
    }

    public function changer(AnnonceLocation $annonce, string $nouveauLoyer): void
    {
        $ancien = $annonce->getLoyer();
        if (bccomp($ancien, $nouveauLoyer, 2) === 0) {
            return;
        }

        foreach ($this->observers as $observer) {
            $observer->onLoyerModifie($annonce, $ancien, $nouveauLoyer);
        }
    }
}
