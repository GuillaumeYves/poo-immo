<?php

declare(strict_types=1);

namespace App\Model\Form;

use App\Model\Annonce\EtatAnnonce;
use App\Model\Bien\CategorieBien;
use App\Model\Bien\TypeBien;
use App\Model\Bien\Ville;

/* *
 * Extracteur de filtres pour l'export des annonces et biens immobiliers, permettant
 * d'extraire et de valider les filtres de recherche à partir des paramètres de
 * requête, en gérant les différentes propriétés des annonces et des biens en
 * fonction de leur type (catégorie, type, transaction, état, ville) et en
 * fournissant des valeurs par défaut ou nulles pour les filtres invalides ou
 * absents. Ce composant est utilisé dans le contrôleur d'export pour préparer les
 * critères de filtrage avant d'effectuer la recherche dans les dépôts.
 */
final class ExportFilterExtractor
{
    public function __construct(
        private readonly DecimalParser $decimals,
    ) {
    }

    /**
     * @param string[] $categories
     * @param string[] $types
     * @param string[] $transactions
     * @param string[] $etats
     * @param string[] $villes
     */
    public function extract(
        array $categories,
        array $types,
        array $transactions,
        array $etats,
        array $villes,
        ?string $prixMax,
    ): array {
        $etatsValides      = array_map(static fn(EtatAnnonce $e): string => $e->value, EtatAnnonce::cases());
        $categoriesValides = array_map(static fn(CategorieBien $c): string => $c->value, CategorieBien::cases());
        $typesValides      = array_map(static fn(TypeBien $t): string => $t->value, TypeBien::cases());
        $villesValides     = array_map(static fn(Ville $v): string => $v->value, Ville::cases());

        return [
            'categorie'   => $this->garderValides($categories, $categoriesValides),
            'type'        => $this->garderValides($types, $typesValides),
            'transaction' => $this->garderValides($transactions, ['vente', 'location']),
            'etat'        => $this->garderValides($etats, $etatsValides),
            'ville'       => $this->garderValides($villes, $villesValides),
            'prixMax'     => $this->parsePrixMax($prixMax),
        ];
    }

    /**
     * @param string[] $valeurs
     * @param string[] $valides
     * @return string[]|null
     */
    private function garderValides(array $valeurs, array $valides): ?array
    {
        $valeurs = array_values(array_unique(array_filter(
            array_map(static fn($v): string => (string) $v, $valeurs),
            static fn(string $v): bool => in_array($v, $valides, true),
        )));

        return $valeurs !== [] ? $valeurs : null;
    }

    private function parsePrixMax(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = str_replace([' ', ','], ['', '.'], $value);
        if ($value === '' || !preg_match('/^\d+(?:\.\d{1,2})?$/', $value)) {
            return null;
        }

        return $this->decimals->format($value);
    }
}
