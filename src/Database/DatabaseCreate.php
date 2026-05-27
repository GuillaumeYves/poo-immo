<?php

declare(strict_types=1);

namespace App\Database;

use App\Repository\AnnonceRepository;

final class DatabaseCreate
{
    public static function create(?string $biensPath = null, ?string $annoncesPath = null): AnnonceRepository
    {
        $biens    = BienSeedLoader::load($biensPath ?? __DIR__ . '/../../data/biens.seed.json');
        $annonces = AnnonceSeedLoader::load($annoncesPath ?? __DIR__ . '/../../data/annonces.seed.json', $biens);

        $repository = new AnnonceRepository();
        foreach ($annonces as $annonce) {
            $repository->add($annonce);
        }

        return $repository;
    }
}
