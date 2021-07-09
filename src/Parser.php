<?php

namespace JKMoney;

use InvalidArgumentException;

use function ltrim;
use function preg_match;
use function sprintf;
use function str_pad;
use function strlen;
use function substr;
use function trim;

class Parser
{
    /** @const string */
    public const DECIMAL_PATTERN = '/^(?P<sign>-)?(?P<digits>0|[1-9]\d*)?\.?(?P<fraction>\d+)?$/';

    public static function parse(string $money): Money
    {
        $decimal = trim($money);

        if ($decimal === '') {
            return new Money(0);
        }

        if (!preg_match(self::DECIMAL_PATTERN, $decimal, $matches) || !isset($matches['digits'])) {
            throw new InvalidArgumentException(
                sprintf(
                    'Cannot parse "%s" to Money.',
                    $decimal
                )
            );
        }

        $negative = isset($matches['sign']) && $matches['sign'] === '-';

        $decimal = $matches['digits'];

        if ($negative) {
            $decimal = '-' . $decimal;
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
            $decimal = '-' . ltrim(substr($decimal, 1), '0');
        } else {
            $decimal = ltrim($decimal, '0');
        }

        if ($decimal === '' || $decimal === '-') {
            $decimal = '0';
        }

        return new Money($decimal);
    }
}
