<?php

declare(strict_types=1);

namespace App\Model\Service;

use App\Model\Annonce\Annonce;
use App\Model\Annonce\EtatAnnonce;
use App\Model\Observer\EtatObserver;

/* *
 * Service pour la gestion des changements d'état des annonces immobilières, permettant
 * de notifier les observateurs enregistrés lorsqu'un changement d'état se produit sur
 * une annonce. Ce service peut être utilisé pour centraliser la logique de gestion des
 * états des annonces et faciliter la notification des différentes parties intéressées
 * lorsque des changements d'état surviennent.
 */
final class EtatService
{
    private array $observers = [];

    public function subscribe(EtatObserver $observer): void
    {
        $this->observers[] = $observer;
    }

    public function changer(Annonce $annonce, EtatAnnonce $nouvelEtat): void
    {
        $ancien = $annonce->getEtat();
        if ($ancien === $nouvelEtat) {
            return;
        }

        foreach ($this->observers as $observer) {
            $observer->onEtatChange($annonce, $ancien, $nouvelEtat);
        }
    }
}
