<?php

declare(strict_types=1);

namespace App\Model\Logger;

use App\Model\Annonce\AnnonceVente;
use App\Model\Observer\PrixObserver;
use DateTimeImmutable;

/* *
 * Logger pour les modifications de prix des annonces de vente immobilière, implémentant
 * l'interface PrixObserver pour être notifié des changements de prix et enregistrer
 * ces événements à l'aide d'une stratégie de logging fournie. Ce logger peut être utilisé
 * pour suivre l'historique des modifications de prix des annonces de vente et fournir
 * des informations utiles pour le débogage ou l'analyse de l'activité de l'application.
 */
final class PrixLogger implements PrixObserver
{
    public function __construct(
        private readonly LoggerStrategy $writer,
    ) {
    }

    public function onPrixModifie(
        AnnonceVente $annonce,
        string $ancienPrix,
        string $nouveauPrix,
    ): void {
        $bien  = $annonce->getBien();
        $sens  = bccomp($nouveauPrix, $ancienPrix, 2) > 0 ? 'hausse' : 'baisse';
        $delta = bcsub($nouveauPrix, $ancienPrix, 2);

        $this->writer->log(sprintf(
            "[%s] Prix %s sur l'annonce id.%d - %s id.%s - Prix initial: %s - Prix courant: %s -> %s (delta %s)",
            (new DateTimeImmutable())->format('Y-m-d H:i:s'),
            $sens,
            $annonce->getId(),
            $bien->getCategorie()->value,
            $bien->getId(),
            $annonce->getPrixInitial(),
            $ancienPrix,
            $nouveauPrix,
            $delta,
        ));
    }
}
