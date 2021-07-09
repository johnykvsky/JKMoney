<?php

namespace JKMoney;

use InvalidArgumentException;
use JsonSerializable;

use function array_fill;
use function array_keys;
use function array_map;
use function array_sum;
use function count;
use function filter_var;
use function floor;
use function is_int;
use function max;
use function str_pad;
use function strlen;
use function substr;

class Money implements JsonSerializable
{
    public const ROUND_HALF_UP = PHP_ROUND_HALF_UP;
    public const ROUND_HALF_DOWN = PHP_ROUND_HALF_DOWN;
    public const ROUND_HALF_EVEN = PHP_ROUND_HALF_EVEN;
    public const ROUND_HALF_ODD = PHP_ROUND_HALF_ODD;
    public const ROUND_UP = 5;
    public const ROUND_DOWN = 6;
    public const ROUND_HALF_POSITIVE_INFINITY = 7;
    public const ROUND_HALF_NEGATIVE_INFINITY = 8;

    /** @const int */
    public const DECIMAL_DIGITS = 2;

    private string $amount;

    /** @var BcMathCalculator */
    private static $calculator;

    /**
     * @param int|string $amount Amount, expressed in the smallest units of currency (eg cents)
     * @throws InvalidArgumentException If amount is not integer
     */
    public function __construct($amount)
    {
        $this->amount = self::prepareAmount($amount);
        self::$calculator = new BcMathCalculator;
    }

    /**
     * @param int|float|string $amount
     */
    private static function prepareAmount($amount): string
    {
        if (filter_var($amount, FILTER_VALIDATE_INT) === false) {
            $numberFromString = Number::fromString((string)$amount);
            if (!$numberFromString->isInteger()) {
                throw new InvalidArgumentException('Amount must be an integer(ish) value');
            }

            $amount = $numberFromString->getIntegerPart();
        }

        return (string)$amount;
    }

    /**
     * @param int|float|string $number
     */
    public static function create($number): Money
    {
        if (is_int($number)) {
            return new self((string)$number);
        }

        if (is_float($number) || is_string($number)) {
            return Parser::parse((string)$number);
        }

        throw new InvalidArgumentException('Invalid amount type provided');
    }

    /**
     * @param int|float|string $amount
     */
    public static function isValid($amount): bool
    {
        try {
            self::create($amount);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public static function min(Money $first, Money ...$collection): Money
    {
        $min = $first;

        foreach ($collection as $money) {
            if ($money->lessThan($min)) {
                $min = $money;
            }
        }

        return $min;
    }

    public function lessThan(Money $other): bool
    {
        return $this->compare($other) < 0;
    }

    /**
     * Returns an integer less than, equal to, or greater than zero
     * if the value of this object is considered to be respectively
     * less than, equal to, or greater than the other.
     *
     */
    public function compare(Money $other): int
    {
        return self::$calculator::compare($this->amount, $other->amount);
    }

    public static function max(Money $first, Money ...$collection): Money
    {
        $max = $first;

        foreach ($collection as $money) {
            if ($money->greaterThan($max)) {
                $max = $money;
            }
        }

        return $max;
    }

    public function greaterThan(Money $other): bool
    {
        return $this->compare($other) > 0;
    }

    public static function sum(Money $first, Money ...$collection): Money
    {
        return $first->add(...$collection);
    }

    public function add(Money ...$addends): Money
    {
        $amount = $this->amount;
        foreach ($addends as $addend) {
            $amount = self::$calculator::add($amount, $addend->amount);
        }

        return new self($amount);
    }

    public static function avg(Money $first, Money ...$collection): Money
    {
        return $first->add(...$collection)->divide(func_num_args());
    }

    /**
     * @param float|int|string $divisor
     */
    public function divide($divisor, int $roundingMode = self::ROUND_HALF_UP): Money
    {
        $this->assertOperand($divisor);
        $this->assertRoundingMode($roundingMode);

        $divisor = Number::fromNumber($divisor)->toString();

        if (self::$calculator::compare($divisor, '0') === 0) {
            throw new InvalidArgumentException('Division by zero');
        }

        $quotient = $this->round(self::$calculator::divide($this->amount, $divisor), $roundingMode);

        return $this->newInstance($quotient);
    }

    /**
     * @param float|int|string $operand
     * @throws InvalidArgumentException If $operand is neither integer nor float
     */
    private function assertOperand($operand): void
    {
        if (!is_numeric($operand)) {
            throw new InvalidArgumentException(
                sprintf('Operand should be a numeric value, "%s" given.', gettype($operand))
            );
        }
    }

    /**
     * @throws InvalidArgumentException If $roundingMode is not valid
     */
    private function assertRoundingMode(int $roundingMode): void
    {
        if (!in_array(
            $roundingMode,
            [
                self::ROUND_HALF_DOWN,
                self::ROUND_HALF_EVEN,
                self::ROUND_HALF_ODD,
                self::ROUND_HALF_UP,
                self::ROUND_UP,
                self::ROUND_DOWN,
                self::ROUND_HALF_POSITIVE_INFINITY,
                self::ROUND_HALF_NEGATIVE_INFINITY,
            ],
            true
        )) {
            throw new InvalidArgumentException(
                'Rounding mode should be Money::ROUND_HALF_DOWN | ' .
                'Money::ROUND_HALF_EVEN | Money::ROUND_HALF_ODD | ' .
                'Money::ROUND_HALF_UP | Money::ROUND_UP | Money::ROUND_DOWN' .
                'Money::ROUND_HALF_POSITIVE_INFINITY | Money::ROUND_HALF_NEGATIVE_INFINITY'
            );
        }
    }

    /**
     * @param int|string $amount
     */
    private function round($amount, int $roundingMode): string
    {
        $this->assertRoundingMode($roundingMode);

        if ($roundingMode === self::ROUND_UP) {
            return self::$calculator::ceil($amount);
        }

        if ($roundingMode === self::ROUND_DOWN) {
            return self::$calculator::floor($amount);
        }

        return self::$calculator::round($amount, $roundingMode);
    }

    /**
     * @param int|string $amount
     */
    private function newInstance($amount): Money
    {
        return new self($amount);
    }

    public function equals(Money $other): bool
    {
        if ($this->amount === $other->amount) {
            return true;
        }

        // @TODO do we want Money instance to be byte-equivalent when trailing zeroes exist? Very expensive!
        // Assumption: Money#equals() is called **less** than other number-based comparisons, and probably
        // only within test suites. Therefore, using complex normalization here is acceptable waste of performance.
        return $this->compare($other) === 0;
    }

    public function greaterThanOrEqual(Money $other): bool
    {
        return $this->compare($other) >= 0;
    }

    public function lessThanOrEqual(Money $other): bool
    {
        return $this->compare($other) <= 0;
    }

    public function getValue(): int
    {
        return (int)$this->amount;
    }

    public function mod(Money $divisor): Money
    {
        return new self(self::$calculator::mod($this->amount, $divisor->amount));
    }

    public function ratioOf(Money $money): string
    {
        if ($money->isZero()) {
            throw new InvalidArgumentException('Cannot calculate a ratio of zero');
        }

        return self::$calculator::divide($this->amount, $money->amount);
    }

    public function isZero(): bool
    {
        return self::$calculator::compare($this->amount, '0') === 0;
    }

    public function absolute(): Money
    {
        return $this->newInstance(self::$calculator::absolute($this->amount));
    }

    public function negative(): Money
    {
        return $this->newInstance(0)->subtract($this);
    }

    public function subtract(Money ...$subtrahends): Money
    {
        $amount = $this->amount;
        foreach ($subtrahends as $subtrahend) {
            $amount = self::$calculator::subtract($amount, $subtrahend->amount);
        }

        return new self($amount);
    }

    public function isPositive(): bool
    {
        return self::$calculator::compare($this->amount, '0') > 0;
    }

    public function isNegative(): bool
    {
        return self::$calculator::compare($this->amount, '0') < 0;
    }

    public function jsonSerialize(): array
    {
        return [
            'amount' => $this->getAmount(),
            'formatted' => $this->getFormatted(),
        ];
    }

    public function getAmount(): string
    {
        return $this->amount;
    }

    public function getFormatted(): string
    {
        return Formatter::format($this);
    }

    /**
     * @param int|float $tax
     */
    public function tax($tax, int $roundingMode = self::ROUND_HALF_UP): Money
    {
        if (!is_numeric($tax) || $tax === 0) {
            throw new InvalidArgumentException('Tax must be (non zero) numeric value');
        }

        $taxValue = $tax / 100;
        return $this->multiply($taxValue, $roundingMode);
    }

    /**
     * @param float|int|string $multiplier
     * @return Money
     */
    public function multiply($multiplier, int $roundingMode = self::ROUND_HALF_UP): Money
    {
        $this->assertOperand($multiplier);
        $this->assertRoundingMode($roundingMode);

        $product = $this->round(self::$calculator::multiply($this->amount, $multiplier), $roundingMode);

        return $this->newInstance($product);
    }
}
