<?php

namespace JKMoney;

use InvalidArgumentException;

class BcMathCalculator
{
    /** @var int */
    private $scale;

    /**
     * @param int $scale
     */
    public function __construct($scale = 14)
    {
        $this->scale = $scale;
    }

    /**
     * @param string $a
     * @param string $b
     * @return int
     */
    public function compare(string $a, string $b): int
    {
        return bccomp($a, $b, $this->scale);
    }

    /**
     * @param string $amount
     * @param string $addend
     * @return string
     */
    public function add(string $amount, string $addend): string
    {
        return Number::fromString(bcadd($amount, $addend, $this->scale))->toString();
    }

    /**
     * @param string $amount
     * @param string $subtrahend
     * @return string
     */
    public function subtract(string $amount, string $subtrahend): string
    {
        return Number::fromString(bcsub($amount, $subtrahend, $this->scale))->toString();
    }

    /**
     * @param string $amount
     * @param int|float|string $multiplierNumber
     * @return string
     */
    public function multiply(string $amount, $multiplierNumber): string
    {
        $multiplier = Number::fromNumber($multiplierNumber);
        return bcmul($amount, $multiplier->toString(), $this->scale);
    }

    /**
     * @param string $amount
     * @param int|float|string $divisorNumber
     * @return string|null
     */
    public function divide(string $amount, $divisorNumber): ?string
    {
        $divisor = Number::fromNumber($divisorNumber);

        return bcdiv($amount, $divisor->toString(), $this->scale);
    }

    /**
     * @param string $amount
     * @return string
     */
    public function ceil(string $amount): string
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

    /**
     * @param string $amount
     * @return string
     */
    public function floor(string $amount): string
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

    /**
     * @param string $number
     * @return string
     */
    public function absolute(string $number): string
    {
        return ltrim($number, '-');
    }

    /**
     * @param int|float|string $amount
     * @param int $roundingMode
     * @return string
     */
    public function round(string $amount, int $roundingMode): string
    {
        $number = Number::fromNumber($amount);

        if ($number->isInteger()) {
            return $number->toString();
        }

        if ($number->isHalf() === false) {
            return $this->roundDigit($number);
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

    /**
     * @param Number $number
     * @return string
     */
    private function roundDigit(Number $number): string
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

    /**
     * @param string $amount
     * @param string $divisor
     * @return string|null
     */
    public function mod(string $amount, string $divisor): ?string
    {
        return bcmod($amount, $divisor);
    }
}
