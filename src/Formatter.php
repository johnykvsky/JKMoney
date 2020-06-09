<?php

namespace JKMoney;

class Formatter
{
    /**
     * @param string $valueBase
     * @return string
     */
    public static function format(string $valueBase): string
    {
        $negative = false;

        if (strpos($valueBase, '-') === 0) {
            $negative = true;
            $valueBase = substr($valueBase, 1);
        }

        $valueLength = strlen($valueBase);

        if ($valueLength > Money::DECIMAL_DIGITS) {
            $formatted = substr($valueBase, 0, $valueLength - Money::DECIMAL_DIGITS);
            $decimalDigits = substr($valueBase, $valueLength - Money::DECIMAL_DIGITS);

            if ($decimalDigits && $decimalDigits !== '') {
                $formatted .= '.' . $decimalDigits;
            }
        } else {
            $formatted = '0.' . str_pad('', Money::DECIMAL_DIGITS - $valueLength, '0') . $valueBase;
        }

        if ($negative === true) {
            $formatted = '-' . $formatted;
        }

        return $formatted;
    }
}
