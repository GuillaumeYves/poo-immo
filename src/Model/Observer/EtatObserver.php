<?php

declare(strict_types=1);

namespace App\Model\Observer;

use App\Model\Annonce\Annonce;
use App\Model\Annonce\EtatAnnonce;

/* *
 * Interface pour les observateurs de changements d'état des annonces immobilières,
 * définissant la méthode onEtatChange qui est appelée lorsqu'un changement d'état
 * se produit sur une annonce. Les classes qui implémentent cette interface peuvent
 * être utilisées pour être notifiées des changements d'état et effectuer des actions
 * en conséquence, telles que l'enregistrement de logs, l'envoi de notifications, etc.
 */
interface EtatObserver
{
    public function onEtatChange(
        Annonce $annonce,
        EtatAnnonce $ancienEtat,
        EtatAnnonce $nouvelEtat,
    ): void;
}
