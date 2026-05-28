<?php

declare(strict_types=1);

namespace App\Logger;

use App\Entity\Annonce\AnnonceVente;
use App\Observer\ReductionObserver;
use App\Reduction\Pourcentage;
use DateTimeImmutable;
use RuntimeException;

final class ReductionLogger implements ReductionObserver
{
    public function __construct(private readonly string $cheminFichier)
    {
        $dossier = dirname($cheminFichier);
        if (!is_dir($dossier) && !mkdir($dossier, 0755, true) && !is_dir($dossier)) {
            throw new RuntimeException("Impossible de créer le dossier de log : {$dossier}");
        }
    }

    public function onReductionAppliquee(
        AnnonceVente $annonce,
        string $ancienPrix,
        string $nouveauPrix,
        Pourcentage $reduction,
    ): void {
        $bien  = $annonce->getBien();
        $ligne = sprintf(
            "[%s] Réduction appliquée à l'annonce id.%d - %s id.%s - Prix initial: %s - Prix courant: %s -> %s (%s)\n",
            (new DateTimeImmutable())->format('Y-m-d H:i:s'),
            $annonce->getId(),
            $bien->getType(),
            $bien->getId(),
            $annonce->getPrixInitial(),
            $ancienPrix,
            $nouveauPrix,
            $reduction->libelle(),
        );

        if (file_put_contents($this->cheminFichier, $ligne, FILE_APPEND | LOCK_EX) === false) {
            throw new RuntimeException("Échec de l'écriture du log : {$this->cheminFichier}");
        }
    }
}
