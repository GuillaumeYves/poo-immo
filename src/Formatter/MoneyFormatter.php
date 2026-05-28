<?php

declare(strict_types=1);

namespace App\Formatter;

final class MoneyFormatter
{
    public function format(string $montant): string
    {
        return number_format((float) $montant, 0, ',', ' ') . ' €';
    }
}
