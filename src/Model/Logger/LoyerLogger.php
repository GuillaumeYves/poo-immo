<?php

declare(strict_types=1);

namespace App\Model\Logger;

use App\Model\Annonce\AnnonceLocation;
use App\Model\Observer\LoyerObserver;
use DateTimeImmutable;

/* *
 * Logger pour les modifications de loyer des annonces de location immobilière, implémentant
 * l'interface LoyerObserver pour être notifié des changements de loyer et enregistrer
 * ces événements à l'aide d'une stratégie de logging fournie. Ce logger peut être utilisé
 * pour suivre l'historique des modifications de loyer des annonces de location et fournir
 * des informations utiles pour le débogage ou l'analyse de l'activité de l'application.
 */
final class LoyerLogger implements LoyerObserver
{
    public function __construct(
        private readonly LoggerStrategy $writer,
    ) {
    }

    public function onLoyerModifie(
        AnnonceLocation $annonce,
        string $ancienLoyer,
        string $nouveauLoyer,
    ): void {
        $bien  = $annonce->getBien();
        $sens  = bccomp($nouveauLoyer, $ancienLoyer, 2) > 0 ? 'hausse' : 'baisse';
        $delta = bcsub($nouveauLoyer, $ancienLoyer, 2);

        $this->writer->log(sprintf(
            "[%s] Loyer %s sur l'annonce id.%d - %s id.%s - Loyer initial: %s - Loyer: %s -> %s (delta %s)",
            (new DateTimeImmutable())->format('Y-m-d H:i:s'),
            $sens,
            $annonce->getId(),
            $bien->getCategorie()->value,
            $bien->getId(),
            $annonce->getLoyerInitial(),
            $ancienLoyer,
            $nouveauLoyer,
            $delta,
        ));
    }
}
