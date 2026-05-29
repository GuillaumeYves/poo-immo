<?php

declare(strict_types=1);

namespace App\Model\Logger;

use App\Model\Annonce\Annonce;
use App\Model\Annonce\EtatAnnonce;
use App\Model\Observer\EtatObserver;
use DateTimeImmutable;

/* *
 * Logger pour les changements d'état des annonces immobilières, implémentant
 * l'interface EtatObserver pour être notifié des changements d'état et enregistrer
 * ces événements à l'aide d'une stratégie de logging fournie. Ce logger peut être
 * utilisé pour suivre l'historique des changements d'état des annonces et fournir
 * des informations utiles pour le débogage ou l'analyse de l'activité de l'application.
 */
final class EtatLogger implements EtatObserver
{
    public function __construct(
        private readonly LoggerStrategy $writer,
    ) {
    }

    public function onEtatChange(
        Annonce $annonce,
        EtatAnnonce $ancienEtat,
        EtatAnnonce $nouvelEtat,
    ): void {
        $bien = $annonce->getBien();

        $this->writer->log(sprintf(
            "[%s] État changé sur l'annonce id.%d - %s id.%s - %s -> %s",
            (new DateTimeImmutable())->format('Y-m-d H:i:s'),
            $annonce->getId(),
            $bien->getCategorie()->value,
            $bien->getId(),
            $ancienEtat->getLibelle(),
            $nouvelEtat->getLibelle(),
        ));
    }
}
