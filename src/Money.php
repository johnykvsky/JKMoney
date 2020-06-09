<?php

namespace JKMoney;

class Money implements \JsonSerializable
{
    const ROUND_HALF_UP = PHP_ROUND_HALF_UP;
    const ROUND_HALF_DOWN = PHP_ROUND_HALF_DOWN;
    const ROUND_HALF_EVEN = PHP_ROUND_HALF_EVEN;
    const ROUND_HALF_ODD = PHP_ROUND_HALF_ODD;
    const ROUND_UP = 5;
    const ROUND_DOWN = 6;
    const ROUND_HALF_POSITIVE_INFINITY = 7;
    const ROUND_HALF_NEGATIVE_INFINITY = 8;

    /** @const int */
    const DECIMAL_DIGITS = 2;

    /** @var string */
    private $amount;

    /** @var BcMathCalculator */
    private $calculator;

    /**
     * @param int|string $amount   Amount, expressed in the smallest units of currency (eg cents)
     * @throws \InvalidArgumentException If amount is not integer
     */
    public function __construct($amount)
    {
        if (filter_var($amount, FILTER_VALIDATE_INT) === false) {
            $numberFromString = Number::fromString($amount);
            if (!$numberFromString->isInteger()) {
                throw new \InvalidArgumentException('Amount must be an integer(ish) value');
            }

            $amount = $numberFromString->getIntegerPart();
        }

        $this->amount = (string) $amount;
        $this->calculator = new BcMathCalculator;
    }

    /**
     * @param int|float|string $number
     * @return Money
     */
    public static function create($number): Money
    {
        if (is_int($number)) {
            return new self((string) $number);
        }

        if (is_float($number) || is_string($number)) {
             return new self((string) Parser::parse($number));
        } 

        throw new \InvalidArgumentException('Invalid amount type provided');
    }

    /**
     * @param int|string $amount
     * @return Money
     */
    private function newInstance($amount): Money
    {
        return new self($amount);
    }

    /**
     * @param Money $other
     * @return bool
     */
    public function equals(Money $other): bool
    {
        return $this->amount === $other->amount;
    }

    /**
     * Returns an integer less than, equal to, or greater than zero
     * if the value of this object is considered to be respectively
     * less than, equal to, or greater than the other.
     *
     * @param Money $other
     * @return int
     */
    public function compare(Money $other): int
    {
        return $this->calculator->compare($this->amount, $other->amount);
    }

    /**
     * @param Money $other
     * @return bool
     */
    public function greaterThan(Money $other): bool
    {
        return $this->compare($other) > 0;
    }

    /**
     * @param \Money\Money $other
     * @return bool
     */
    public function greaterThanOrEqual(Money $other): bool
    {
        return $this->compare($other) >= 0;
    }

    /**
     * @param Money $other
     * @return bool
     */
    public function lessThan(Money $other): bool
    {
        return $this->compare($other) < 0;
    }

    /**
     * @param \Money\Money $other
     * @return bool
     */
    public function lessThanOrEqual(Money $other): bool
    {
        return $this->compare($other) <= 0;
    }

    /**
     * @return string
     */
    public function getAmount(): string
    {
        return $this->amount;
    }

    /**
     * @return int
     */
    public function getValue(): int
    {
        return (int) $this->amount;
    }

    public function getString(): string
    {
        return Formatter::format($this->amount);
    }

    /**
     * @param Money[] $addends
     * @return Money
     */
    public function add(Money ...$addends): Money
    {
        $amount = $this->amount;
        foreach ($addends as $addend) {
            $amount = $this->calculator->add($amount, $addend->amount);
        }

        return new self($amount);
    }

    /**
     * @param Money[] $subtrahends
     * @return Money
     */
    public function subtract(Money ...$subtrahends): Money
    {
        $amount = $this->amount;
        foreach ($subtrahends as $subtrahend) {
            $amount = $this->calculator->subtract($amount, $subtrahend->amount);
        }

        return new self($amount);
    }

    /**
     * @param float|int|string $operand
     * @throws \InvalidArgumentException If $operand is neither integer nor float
     */
    private function assertOperand($operand)
    {
        if (!is_numeric($operand)) {
            throw new \InvalidArgumentException(sprintf(
                'Operand should be a numeric value, "%s" given.',
                is_object($operand) ? get_class($operand) : gettype($operand)
            ));
        }
    }

    /**
     * @param int $roundingMode
     * @throws \InvalidArgumentException If $roundingMode is not valid
     */
    private function assertRoundingMode($roundingMode)
    {
        if (!in_array(
            $roundingMode, [
                self::ROUND_HALF_DOWN, self::ROUND_HALF_EVEN, self::ROUND_HALF_ODD,
                self::ROUND_HALF_UP, self::ROUND_UP, self::ROUND_DOWN,
                self::ROUND_HALF_POSITIVE_INFINITY, self::ROUND_HALF_NEGATIVE_INFINITY,
            ], true
        )) {
            throw new \InvalidArgumentException(
                'Rounding mode should be Money::ROUND_HALF_DOWN | '.
                'Money::ROUND_HALF_EVEN | Money::ROUND_HALF_ODD | '.
                'Money::ROUND_HALF_UP | Money::ROUND_UP | Money::ROUND_DOWN'.
                'Money::ROUND_HALF_POSITIVE_INFINITY | Money::ROUND_HALF_NEGATIVE_INFINITY'
            );
        }
    }

    /**
     * @param float|int|string $multiplier
     * @param int $roundingMode
     * @return Money
     */
    public function multiply($multiplier, $roundingMode = self::ROUND_HALF_UP): Money
    {
        $this->assertOperand($multiplier);
        $this->assertRoundingMode($roundingMode);

        $product = $this->round($this->calculator->multiply($this->amount, $multiplier), $roundingMode);

        return $this->newInstance($product);
    }

    /**
     * @param float|int|string $divisor
     * @param int $roundingMode
     * @return Money
     */
    public function divide($divisor, $roundingMode = self::ROUND_HALF_UP): Money
    {
        $this->assertOperand($divisor);
        $this->assertRoundingMode($roundingMode);

        $divisor = (string) Number::fromNumber($divisor);

        if ($this->calculator->compare($divisor, '0') === 0) {
            throw new \InvalidArgumentException('Division by zero');
        }

        $quotient = $this->round($this->calculator->divide($this->amount, $divisor), $roundingMode);

        return $this->newInstance($quotient);
    }

    /**
     * @param Money $divisor
     * @return Money
     */
    public function mod(Money $divisor): Money
    {
        return new self($this->calculator->mod($this->amount, $divisor->amount));
    }

  
    /**
     * @param Money $money
     * @return string
     */
    public function ratioOf(Money $money): string
    {
        if ($money->isZero()) {
            throw new \InvalidArgumentException('Cannot calculate a ratio of zero');
        }

        return $this->calculator->divide($this->amount, $money->amount);
    }

    /**
     * @param string $amount
     * @param int $rounding_mode
     * @return string
     */
    private function round($amount, $rounding_mode): string
    {
        $this->assertRoundingMode($rounding_mode);

        if ($rounding_mode === self::ROUND_UP) {
            return $this->calculator->ceil($amount);
        }

        if ($rounding_mode === self::ROUND_DOWN) {
            return $this->calculator->floor($amount);
        }

        return $this->calculator->round($amount, $rounding_mode);
    }

    /**
     * @return Money
     */
    public function absolute(): Money
    {
        return $this->newInstance($this->calculator->absolute($this->amount));
    }

    /**
     * @return Money
     */
    public function negative(): Money
    {
        return $this->newInstance(0)->subtract($this);
    }

    /**
     * Checks if the value represented by this object is zero.
     *
     * @return bool
     */
    public function isZero(): bool
    {
        return $this->calculator->compare($this->amount, 0) === 0;
    }

    /**
     * Checks if the value represented by this object is positive.
     *
     * @return bool
     */
    public function isPositive(): bool
    {
        return $this->calculator->compare($this->amount, 0) > 0;
    }

    /**
     * Checks if the value represented by this object is negative.
     *
     * @return bool
     */
    public function isNegative(): bool
    {
        return $this->calculator->compare($this->amount, 0) < 0;
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'amount' => $this->amount
        ];
    }
}
