<?php

namespace JKMoney;

use function assert;
use function str_pad;
use function strlen;
use function substr;

class Formatter
{
    public static function format(Money $money): string
    {
        $valueBase = $money->getAmount();
        $negative = $valueBase[0] === '-';

        if ($negative) {
            $valueBase = substr($valueBase, 1);
        }

        $valueLength = strlen($valueBase);

        if ($valueLength > Money::DECIMAL_DIGITS) {
            $formatted = substr($valueBase, 0, $valueLength - Money::DECIMAL_DIGITS);
            $decimalDigits = substr($valueBase, $valueLength - Money::DECIMAL_DIGITS);

            if (strlen($decimalDigits) > 0) {
                $formatted .= '.' . $decimalDigits;
            }
        } else {
            $formatted = '0.' . str_pad('', Money::DECIMAL_DIGITS - $valueLength, '0') . $valueBase;
        }

        if ($negative) {
            return '-' . $formatted;
        }

        assert($formatted !== '');

        return $formatted;
    }
}
