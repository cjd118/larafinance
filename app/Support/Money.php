<?php

namespace App\Support;

use InvalidArgumentException;

class Money
{
    public static function toPence(string $value): int
    {
        if (!preg_match('/^-?\d+(\.\d{1,2})?$/', $value)) {
            throw new InvalidArgumentException("Invalid GBP decimal string: {$value}");
        }

        $negative = str_starts_with($value, '-');
        $value = ltrim($value, '-');

        [$pounds, $pence] = array_pad(explode('.', $value, 2), 2, '00');
        $pence = str_pad($pence, 2, '0', STR_PAD_RIGHT);
        $total = (int) ($pounds . $pence);

        return $negative ? -$total : $total;
    }
}
