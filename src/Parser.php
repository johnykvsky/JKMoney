<?php

namespace JKMoney;

class Parser
{
    /** @const string */
    const DECIMAL_PATTERN = '/^(?P<sign>-)?(?P<digits>0|[1-9]\d*)?\.?(?P<fraction>\d+)?$/';

    /**
     * @param string $money
     * @return string
     */
    public static function parse(string $money): string
    {
        if (!is_string($money)) {
            throw new \InvalidArgumentException('Formatted raw money should be string, e.g. 1.00');
        }

        $decimal = trim($money);

        if ($decimal === '') {
            return '0';
        }

        if (!preg_match(self::DECIMAL_PATTERN, $decimal, $matches) || !isset($matches['digits'])) {
            throw new \InvalidArgumentException(sprintf(
                'Cannot parse "%s" to Money.',
                $decimal
            ));
        }

        $negative = isset($matches['sign']) && $matches['sign'] === '-';

        $decimal = $matches['digits'];

        if ($negative) {
            $decimal = '-'.$decimal;
        }

        if (isset($matches['fraction'])) {
            $fractionDigits = strlen($matches['fraction']);
            $decimal .= $matches['fraction'];
            $decimal = Number::roundMoneyValue($decimal, Money::DECIMAL_DIGITS, $fractionDigits);

            if ($fractionDigits > Money::DECIMAL_DIGITS) {
                $decimal = substr($decimal, 0, Money::DECIMAL_DIGITS - $fractionDigits);
            } elseif ($fractionDigits < Money::DECIMAL_DIGITS) {
                $decimal .= str_pad('', Money::DECIMAL_DIGITS - $fractionDigits, '0');
            }
        } else {
            $decimal .= str_pad('', Money::DECIMAL_DIGITS, '0');
        }

        if ($negative) {
            $decimal = '-'.ltrim(substr($decimal, 1), '0');
        } else {
            $decimal = ltrim($decimal, '0');
        }

        if ($decimal === '' || $decimal === '-') {
            $decimal = '0';
        }

        return $decimal;
    }
}
