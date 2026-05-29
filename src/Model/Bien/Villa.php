<?php

declare(strict_types=1);

namespace App\Model\Bien;

/* *
 * Représente une villa, qui est un type de maison, avec des informations
 * spécifiques à une villa. Cette classe hérite de Maison et redéfinit la
 * méthode getCategorie pour retourner la catégorie spécifique de Villa.
 */
final class Villa extends Maison
{
    public function getCategorie(): CategorieBien
    {
        return CategorieBien::Villa;
    }
}
