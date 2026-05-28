<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Annonce\AnnonceRepositoryInterface;
use App\Entity\Annonce\AnnonceVente;
use App\Observer\ReductionObserver;
use App\Reduction\Pourcentage;
use RuntimeException;


final class AppliqueReduction
{
    /** @var ReductionObserver[] */
    private array $observers = [];

    public function __construct(
        private readonly AnnonceRepositoryInterface $repository,
    ) {
    }

    public function subscribe(ReductionObserver $observer): void
    {
        $this->observers[] = $observer;
    }

    public function appliquer(int $annonceId, string $pourcentage): void
    {
        $annonce = $this->repository->findById($annonceId)
            ?? throw new RuntimeException("Annonce introuvable : id={$annonceId}");

        if (!$annonce instanceof AnnonceVente) {
            throw new RuntimeException(
                "Les réductions ne s'appliquent qu'aux annonces de vente (id={$annonceId} est une "
                . $annonce->getTypeTransaction() . ')'
            );
        }

        if (!$annonce->getEtat()->estActive()) {
            throw new RuntimeException("Impossible d'appliquer une réduction sur une annonce indisponible (id={$annonceId}).");
        }

        $reduction   = new Pourcentage($pourcentage);
        $ancienPrix  = $annonce->getPrixCourant();
        $nouveauPrix = $reduction->appliquer($annonce->getPrixInitial());

        $this->repository->updatePrixCourant($annonceId, $nouveauPrix);

        $this->notifier($annonce, $ancienPrix, $nouveauPrix, $reduction);
    }

    private function notifier(
        AnnonceVente $annonce,
        string $ancienPrix,
        string $nouveauPrix,
        Pourcentage $reduction,
    ): void {
        foreach ($this->observers as $observer) {
            $observer->onReductionAppliquee($annonce, $ancienPrix, $nouveauPrix, $reduction);
        }
    }
}
