<?php

namespace JKMoney;

class BcMathCalculator
{
    /** @var string */
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
    public function compare($a, $b): int
    {
        return bccomp($a, $b, $this->scale);
    }

    /**
     * @param string $amount
     * @param string $addend
     * @return string
     */
    public function add($amount, $addend): string
    {
        return (string) Number::fromString(bcadd($amount, $addend, $this->scale));
    }

    /**
     * @param string $amount
     * @param string $subtrahend
     * @return string
     */
    public function subtract($amount, $subtrahend): string
    {
        return (string) Number::fromString(bcsub($amount, $subtrahend, $this->scale));
    }

    /**
     * @param string $amount
     * @param int|float|string $multiplier
     * @return string
     */
    public function multiply($amount, $multiplier): string
    {
        $multiplier = Number::fromNumber($multiplier);
        return bcmul($amount, (string) $multiplier, $this->scale);
    }

    /**
     * @param string $amount
     * @param int|float|string $divisor
     * @return string
     */
    public function divide($amount, $divisor): string
    {
        $divisor = Number::fromNumber($divisor);

        return bcdiv($amount, (string) $divisor, $this->scale);
    }

    /**
     * @param string $number
     * @return string
     */
    public function ceil($number): string
    {
        $number = Number::fromNumber($number);

        if ($number->isInteger()) {
            return (string) $number;
        }

        if ($number->isNegative()) {
            return bcadd((string) $number, '0', 0);
        }

        return bcadd((string) $number, '1', 0);
    }

    /**
     * @param string $number
     * @return string
     */
    public function floor($number): string
    {
        $number = Number::fromNumber($number);

        if ($number->isInteger()) {
            return (string) $number;
        }

        if ($number->isNegative()) {
            return bcadd((string) $number, '-1', 0);
        }

        return bcadd($number, '0', 0);
    }

    /**
     * @param string $number
     * @return string
     */
    public function absolute($number): string
    {
        return ltrim($number, '-');
    }

    /**
     * @param int|float|string $number
     * @param int $roundingMode
     * @return string
     */
    public function round($number, $roundingMode): string
    {
        $number = Number::fromNumber($number);

        if ($number->isInteger()) {
            return (string) $number;
        }

        if ($number->isHalf() === false) {
            return $this->roundDigit($number);
        }

        if (Money::ROUND_HALF_UP === $roundingMode) {
            return bcadd(
                (string) $number,
                $number->getIntegerRoundingMultiplier(),
                0
            );
        }

        if (Money::ROUND_HALF_DOWN === $roundingMode) {
            return bcadd((string) $number, '0', 0);
        }

        if (Money::ROUND_HALF_EVEN === $roundingMode) {
            if ($number->isCurrentEven()) {
                return bcadd((string) $number, '0', 0);
            }

            return bcadd(
                (string) $number,
                $number->getIntegerRoundingMultiplier(),
                0
            );
        }

        if (Money::ROUND_HALF_ODD === $roundingMode) {
            if ($number->isCurrentEven()) {
                return bcadd(
                    (string) $number,
                    $number->getIntegerRoundingMultiplier(),
                    0
                );
            }

            return bcadd((string) $number, '0', 0);
        }

        if (Money::ROUND_HALF_POSITIVE_INFINITY === $roundingMode) {
            if ($number->isNegative()) {
                return bcadd((string) $number, '0', 0);
            }

            return bcadd(
                (string) $number,
                $number->getIntegerRoundingMultiplier(),
                0
            );
        }

        if (Money::ROUND_HALF_NEGATIVE_INFINITY === $roundingMode) {
            if ($number->isNegative()) {
                return bcadd(
                    (string) $number,
                    $number->getIntegerRoundingMultiplier(),
                    0
                );
            }

            return bcadd(
                (string) $number,
                '0',
                0
            );
        }

        throw new \InvalidArgumentException('Unknown rounding mode');
    }

    /**
     * @param $number
     * @return string
     */
    private function roundDigit(Number $number)
    {
        if ($number->isCloserToNext()) {
            return bcadd(
                (string) $number,
                $number->getIntegerRoundingMultiplier(),
                0
            );
        }

        return bcadd((string) $number, '0', 0);
    }

    /**
     * @param string $amount
     * @param int|float|string $divisor
     * @return string
     */
    public function mod($amount, $divisor): string
    {
        return bcmod($amount, $divisor);
    }
}
