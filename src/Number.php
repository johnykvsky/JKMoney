<?php

namespace JKMoney;

class Number
{
    /** @var string */
    private $integerPart;

    /** @var string */
    private $fractionalPart;

    /** @var array */
    private static $numbers = [0 => 1, 1 => 1, 2 => 1, 3 => 1, 4 => 1, 5 => 1, 6 => 1, 7 => 1, 8 => 1, 9 => 1];

    /**
     * @param string $integerPart
     * @param string $fractionalPart
     */
    public function __construct($integerPart, $fractionalPart = '')
    {
        if ('' === $integerPart && '' === $fractionalPart) {
            throw new \InvalidArgumentException('Empty number is invalid');
        }

        $this->integerPart = $this->parseIntegerPart((string) $integerPart);
        $this->fractionalPart = $this->parseFractionalPart((string) $fractionalPart);
    }

    /**
     * @param $number
     * @return self
     */
    public static function fromString($number): self
    {
        $decimalSeparatorPosition = strpos($number, '.');
        if ($decimalSeparatorPosition === false) {
            return new self($number, '');
        }

        return new self(
            substr($number, 0, $decimalSeparatorPosition),
            rtrim(substr($number, $decimalSeparatorPosition + 1), '0')
        );
    }

    /**
     * @param int $number
     * @return self
     */
    public static function fromInt($number): self
    {
        if (is_int($number) === false) {
            throw new \InvalidArgumentException('Integer value expected');
        }

        return new self($number);
    }

    /**
     * @param float $number
     * @return self
     */
    public static function fromFloat($number): self
    {
        if (is_float($number) === false) {
            throw new \InvalidArgumentException('Floating point value expected');
        }

        return self::fromString(sprintf('%.14F', $number));
    }

    /**
     * @param float|int|string $number
     * @return self
     */
    public static function fromNumber($number): self
    {
        if (is_float($number)) {
            return self::fromString(sprintf('%.14F', $number));
        }

        if (is_int($number)) {
            return new self($number);
        }

        if (is_string($number)) {
            return self::fromString($number);
        }

        throw new \InvalidArgumentException('Valid numeric value expected');
    }

    /**
     * @return bool
     */
    public function isDecimal(): bool
    {
        return $this->fractionalPart !== '';
    }

    /**
     * @return bool
     */
    public function isInteger(): bool
    {
        return $this->fractionalPart === '';
    }

    /**
     * @return bool
     */
    public function isHalf(): bool
    {
        return $this->fractionalPart === '5';
    }

    /**
     * @return bool
     */
    public function isCurrentEven(): bool
    {
        $lastIntegerPartNumber = $this->integerPart[strlen($this->integerPart) - 1];

        return $lastIntegerPartNumber % 2 === 0;
    }

    /**
     * @return bool
     */
    public function isCloserToNext(): bool
    {
        if ($this->fractionalPart === '') {
            return false;
        }

        return $this->fractionalPart[0] >= 5;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if ($this->fractionalPart === '') {
            return $this->integerPart;
        }

        return $this->integerPart.'.'.$this->fractionalPart;
    }

    /**
     * @return bool
     */
    public function isNegative(): bool
    {
        return $this->integerPart[0] === '-';
    }

    /**
     * @return string
     */
    public function getIntegerPart(): string
    {
        return $this->integerPart;
    }

    /**
     * @return string
     */
    public function getFractionalPart(): string
    {
        return $this->fractionalPart;
    }

    /**
     * @return string
     */
    public function getIntegerRoundingMultiplier(): string
    {
        if ($this->integerPart[0] === '-') {
            return '-1';
        }

        return '1';
    }

    /**
     * @param string $number
     *
     * @return string
     */
    private static function parseIntegerPart($number): string
    {
        if ('' === $number || '0' === $number) {
            return '0';
        }

        if ('-' === $number) {
            return '-0';
        }

        $nonZero = false;

        for ($position = 0, $characters = strlen($number); $position < $characters; ++$position) {
            $digit = $number[$position];

            if (!isset(static::$numbers[$digit]) && !(0 === $position && '-' === $digit)) {
                throw new \InvalidArgumentException(
                    sprintf('Invalid integer part %1$s. Invalid digit %2$s found', $number, $digit)
                );
            }

            if (false === $nonZero && '0' === $digit) {
                throw new \InvalidArgumentException(
                    'Leading zeros are not allowed'
                );
            }

            $nonZero = true;
        }

        return $number;
    }

    /**
     * @param string $number
     * @return string
     */
    private static function parseFractionalPart($number): string
    {
        if ('' === $number) {
            return $number;
        }

        for ($position = 0, $characters = strlen($number); $position < $characters; ++$position) {
            $digit = $number[$position];
            if (!isset(static::$numbers[$digit])) {
                throw new \InvalidArgumentException(
                    sprintf('Invalid fractional part %1$s. Invalid digit %2$s found', $number, $digit)
                );
            }
        }

        return $number;
    }

    /**
     * @param string $moneyValue
     * @param int    $targetDigits
     * @param int    $havingDigits
     *
     * @return string
     */
    public static function roundMoneyValue($moneyValue, $targetDigits, $havingDigits): string
    {
        $valueLength = strlen($moneyValue);
        $shouldRound = $targetDigits < $havingDigits && $valueLength - $havingDigits + $targetDigits > 0;

        if ($shouldRound && $moneyValue[$valueLength - $havingDigits + $targetDigits] >= 5) {
            $position = $valueLength - $havingDigits + $targetDigits;
            $addend = 1;

            while ($position > 0) {
                $newValue = (string) ((int) $moneyValue[$position - 1] + $addend);

                if ($newValue >= 10) {
                    $moneyValue[$position - 1] = $newValue[1];
                    $addend = $newValue[0];
                    --$position;
                    if ($position === 0) {
                        $moneyValue = $addend.$moneyValue;
                    }
                } else {
                    if ($moneyValue[$position - 1] === '-') {
                        $moneyValue[$position - 1] = $newValue[0];
                        $moneyValue = '-'.$moneyValue;
                    } else {
                        $moneyValue[$position - 1] = $newValue[0];
                    }

                    break;
                }
            }
        }

        return $moneyValue;
    }
}
