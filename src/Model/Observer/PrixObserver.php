<?php

declare(strict_types=1);

namespace App\Model\Observer;

use App\Model\Annonce\AnnonceVente;

/* *
 * Interface pour les observateurs de modifications de prix des annonces de vente
 * immobilière, définissant la méthode onPrixModifie qui est appelée lorsqu'un changement
 * de prix se produit sur une annonce de vente. Les classes qui implémentent cette
 * interface peuvent être utilisées pour être notifiées des changements de prix et
 * effectuer des actions en conséquence, telles que l'enregistrement de logs, l'envoi
 * de notifications, etc.
 */
interface PrixObserver
{
    public function onPrixModifie(
        AnnonceVente $annonce,
        string $ancienPrix,
        string $nouveauPrix,
    ): void;
}
