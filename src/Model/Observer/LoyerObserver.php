<?php

declare(strict_types=1);

namespace App\Model\Observer;

use App\Model\Annonce\AnnonceLocation;

/* *
 * Interface pour les observateurs de modifications de loyer des annonces de location
 * immobilière, définissant la méthode onLoyerModifie qui est appelée lorsqu'un changement
 * de loyer se produit sur une annonce de location. Les classes qui implémentent cette
 * interface peuvent être utilisées pour être notifiées des changements de loyer et
 * effectuer des actions en conséquence, telles que l'enregistrement de logs, l'envoi
 * de notifications, etc.
 */
interface LoyerObserver
{
    public function onLoyerModifie(
        AnnonceLocation $annonce,
        string $ancienLoyer,
        string $nouveauLoyer,
    ): void;
}
