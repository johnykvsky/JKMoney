<?php

namespace JKMoney;

use InvalidArgumentException;

use function bcadd;
use function bccomp;
use function bcdiv;
use function bcmod;
use function bcmul;
use function bcsub;
use function ltrim;

class BcMathCalculator
{
    private const SCALE = 14;

    public static function compare(string $a, string $b): int
    {
        return bccomp($a, $b, self::SCALE);
    }

    public static function add(string $amount, string $addend): string
    {
        return Number::fromString(\bcadd($amount, $addend, self::SCALE))->toString();
    }

    function subtract(string $amount, string $subtrahend): string
    {
        return Number::fromString(\bcsub($amount, $subtrahend, self::SCALE))->toString();
    }

    public static function multiply(string $amount, $multiplierNumber): string
    {
        $multiplier = Number::fromNumber($multiplierNumber);
        return bcmul($amount, $multiplier->toString(), self::SCALE);
    }

    public static function divide(string $amount, $divisorNumber): ?string
    {
        $divisor = Number::fromNumber($divisorNumber);

        return bcdiv($amount, $divisor->toString(), self::SCALE);
    }

    public static function ceil(string $amount): string
    {
        $number = Number::fromNumber($amount);

        if ($number->isInteger()) {
            return $number->toString();
        }

        if ($number->isNegative()) {
            return bcadd($number->toString(), '0', 0);
        }

        return bcadd($number->toString(), '1', 0);
    }

    public static function floor(string $amount): string
    {
        $number = Number::fromNumber($amount);

        if ($number->isInteger()) {
            return $number->toString();
        }

        if ($number->isNegative()) {
            return bcadd($number->toString(), '-1', 0);
        }

        return bcadd($number, '0', 0);
    }

    public static function absolute(string $number): string
    {
        return ltrim($number, '-');
    }

    public static function round(string $amount, int $roundingMode): string
    {
        $number = Number::fromNumber($amount);

        if ($number->isInteger()) {
            return $number->toString();
        }

        if ($number->isHalf() === false) {
            return self::roundDigit($number);
        }

        if (Money::ROUND_HALF_UP === $roundingMode) {
            return bcadd(
                $number->toString(),
                $number->getIntegerRoundingMultiplier(),
                0
            );
        }

        if (Money::ROUND_HALF_DOWN === $roundingMode) {
            return bcadd($number->toString(), '0', 0);
        }

        if (Money::ROUND_HALF_EVEN === $roundingMode) {
            if ($number->isCurrentEven()) {
                return bcadd($number->toString(), '0', 0);
            }

            return bcadd(
                $number->toString(),
                $number->getIntegerRoundingMultiplier(),
                0
            );
        }

        if (Money::ROUND_HALF_ODD === $roundingMode) {
            if ($number->isCurrentEven()) {
                return bcadd(
                    $number->toString(),
                    $number->getIntegerRoundingMultiplier(),
                    0
                );
            }

            return bcadd($number->toString(), '0', 0);
        }

        if (Money::ROUND_HALF_POSITIVE_INFINITY === $roundingMode) {
            if ($number->isNegative()) {
                return bcadd($number->toString(), '0', 0);
            }

            return bcadd(
                $number->toString(),
                $number->getIntegerRoundingMultiplier(),
                0
            );
        }

        if (Money::ROUND_HALF_NEGATIVE_INFINITY === $roundingMode) {
            if ($number->isNegative()) {
                return bcadd(
                    $number->toString(),
                    $number->getIntegerRoundingMultiplier(),
                    0
                );
            }

            return bcadd(
                $number->toString(),
                '0',
                0
            );
        }

        throw new InvalidArgumentException('Unknown rounding mode');
    }

    private static function roundDigit(Number $number): string
    {
        if ($number->isCloserToNext()) {
            return bcadd(
                $number->toString(),
                $number->getIntegerRoundingMultiplier(),
                0
            );
        }

        return bcadd($number->toString(), '0', 0);
    }

    public static function mod(string $amount, string $divisor): ?string
    {
        return bcmod($amount, $divisor);
    }
}
