<?php

use JKMoney\Money;
use PHPUnit\Framework\TestCase;

final class MoneyTest extends TestCase
{
    use AggregateExamples;
    use RoundExamples;

    const AMOUNT = 10;

    const OTHER_AMOUNT = 5;

    /**
     * @dataProvider equalityExamples
     * @test
     */
    public function it_equals_to_another_money($amount, $equality)
    {
        $money = new Money(self::AMOUNT);

        $this->assertEquals($equality, $money->equals(new Money($amount)));
    }

    /**
     * @dataProvider comparisonExamples
     * @test
     */
    public function it_compares_two_amounts($other, $result)
    {
        $money = new Money(self::AMOUNT);
        $other = new Money($other);

        $this->assertEquals($result, $money->compare($other));
        $this->assertEquals(1 === $result, $money->greaterThan($other));
        $this->assertEquals(0 <= $result, $money->greaterThanOrEqual($other));
        $this->assertEquals(-1 === $result, $money->lessThan($other));
        $this->assertEquals(0 >= $result, $money->lessThanOrEqual($other));

        if ($result === 0) {
            $this->assertEquals($money, $other);
        } else {
            $this->assertNotEquals($money, $other);
        }
    }

    /**
     * @dataProvider roundExamples
     * @test
     */
    public function it_multiplies_the_amount($multiplier, $roundingMode, $result)
    {
        $money = new Money(1);

        $money = $money->multiply($multiplier, $roundingMode);

        $this->assertInstanceOf(Money::class, $money);
        $this->assertEquals((string) $result, $money->getAmount());
    }

    /**
     * @dataProvider invalidOperandExamples
     * @test
     */
    public function it_throws_an_exception_when_operand_is_invalid_during_multiplication($operand)
    {
        $this->expectException(\InvalidArgumentException::class);

        $money = new Money(1);

        $money->multiply($operand);
    }

    /**
     * @dataProvider roundExamples
     */
    public function it_divides_the_amount($divisor, $roundingMode, $result)
    {
        $money = new Money(1);

        $money = $money->divide(1 / $divisor, $roundingMode);

        $this->assertInstanceOf(Money::class, $money);
        $this->assertEquals((string) $result, $money->getAmount());
    }

    /**
     * @dataProvider invalidOperandExamples
     * @test
     */
    public function it_throws_an_exception_when_operand_is_invalid_during_division($operand)
    {
        $this->expectException(\InvalidArgumentException::class);

        $money = new Money(1);

        $money->divide($operand);
    }

    /**
     * @dataProvider comparatorExamples
     * @test
     */
    public function it_has_comparators($amount, $isZero, $isPositive, $isNegative)
    {
        $money = new Money($amount);

        $this->assertEquals($isZero, $money->isZero());
        $this->assertEquals($isPositive, $money->isPositive());
        $this->assertEquals($isNegative, $money->isNegative());
    }

    /**
     * @dataProvider absoluteExamples
     * @test
     */
    public function it_calculates_the_absolute_amount($amount, $result)
    {
        $money = new Money($amount);

        $money = $money->absolute();

        $this->assertEquals($result, $money->getAmount());
    }

    /**
     * @dataProvider negativeExamples
     * @test
     */
    public function it_calculates_the_negative_amount($amount, $result)
    {
        $money = new Money($amount);

        $money = $money->negative();

        $this->assertEquals($result, $money->getAmount());
    }

    /**
     * @dataProvider modExamples
     * @test
     */
    public function it_calculates_the_modulus_of_an_amount($left, $right, $expected)
    {
        $money = new Money($left);
        $rightMoney = new Money($right);

        $money = $money->mod($rightMoney);

        $this->assertInstanceOf(Money::class, $money);
        $this->assertEquals($expected, $money->getAmount());
    }

    /**
     * @test
     */
    public function it_converts_to_json()
    {
        $this->assertEquals(
            '{"amount":"350"}',
            json_encode(Money::create(350))
        );

        $this->assertEquals(
            ['amount' => '350'],
            Money::create(350)->jsonSerialize()
        );
    }

    /**
     * @test
     */
    public function it_supports_max_int()
    {
        $one = new Money(1);

        $this->assertInstanceOf(Money::class, new Money(PHP_INT_MAX));
        $this->assertInstanceOf(Money::class, (new Money(PHP_INT_MAX))->add($one));
        $this->assertInstanceOf(Money::class, (new Money(PHP_INT_MAX))->subtract($one));
    }

    /**
     * @test
     */
    public function it_returns_ratio_of()
    {
        $zero = new Money(0);
        $three = new Money(3);
        $six = new Money(6);

        $this->assertEquals(0, $zero->ratioOf($six));
        $this->assertEquals(0.5, $three->ratioOf($six));
        $this->assertEquals(1, $three->ratioOf($three));
        $this->assertEquals(2, $six->ratioOf($three));
    }

    /**
     * @test
     */
    public function it_throws_when_calculating_ratio_of_zero()
    {
        $this->expectException(\InvalidArgumentException::class);

        $zero = new Money(0);
        $six = new Money(6);

        $six->ratioOf($zero);
    }

    public function equalityExamples()
    {
        return [
            [self::AMOUNT, true],
            [self::AMOUNT + 1, false],
            [(string) self::AMOUNT, true],
            [((string) self::AMOUNT).'.000', true],
        ];
    }

    public function comparisonExamples()
    {
        return [
            [self::AMOUNT, 0],
            [self::AMOUNT - 1, 1],
            [self::AMOUNT + 1, -1],
        ];
    }

    public function invalidOperandExamples()
    {
        return [
            [[]],
            [false],
            ['operand'],
            [null],
            [new \stdClass()],
        ];
    }

    public function comparatorExamples()
    {
        return [
            [1, false, true, false],
            [0, true, false, false],
            [-1, false, false, true],
            ['1', false, true, false],
            ['0', true, false, false],
            ['-1', false, false, true],
        ];
    }

    public function absoluteExamples()
    {
        return [
            [1, 1],
            [0, 0],
            [-1, 1],
            ['1', 1],
            ['0', 0],
            ['-1', 1],
        ];
    }

    public function negativeExamples()
    {
        return [
            [1, -1],
            [0, 0],
            [-1, 1],
            ['1', -1],
            ['0', 0],
            ['-1', 1],
        ];
    }

    public function modExamples()
    {
        return [
            [11, 5, '1'],
            [9, 3, '0'],
            [1006, 10, '6'],
            [1007, 10, '7'],
        ];
    }
}
