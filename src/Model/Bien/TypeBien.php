<?php

declare(strict_types=1);

namespace App\Model\Bien;

/* *
 * Enumération représentant les différents types de biens immobiliers, avec
 * des méthodes pour obtenir un libellé lisible de chaque type.
 */
enum TypeBien: string
{
    case Studio = 'Studio';
    case T1     = 'T1';
    case T2     = 'T2';
    case T3     = 'T3';
    case T4     = 'T4';
    case T5     = 'T5';
    case T6     = 'T6';
    case T7Plus = 'T7+';

    public function libelle(): string
    {
        return $this->value;
    }
}
