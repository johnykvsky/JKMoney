<?php

use JKMoney\BcMathCalculator;
use PHPUnit\Framework\TestCase;

abstract class CalculatorTestCase extends TestCase
{
    use RoundExamples;

    /**
     * @dataProvider additionExamples
     * @test
     */
    public function it_adds_two_values($value1, $value2, $expected)
    {
        $this->assertEquals($expected, BcMathCalculator::add($value1, $value2));
    }

    /**
     * @dataProvider subtractionExamples
     * @test
     */
    public function it_subtracts_a_value_from_another($value1, $value2, $expected)
    {
        $this->assertEquals($expected, BcMathCalculator::subtract($value1, $value2));
    }

    /**
     * @dataProvider multiplicationExamples
     * @test
     */
    public function it_multiplies_a_value_by_another($value1, $value2, $expected)
    {
        $this->assertEquals($expected,
            //fix for bcmul PHP 7.3 vs later versions
            str_pad(rtrim(BcMathCalculator::multiply($value1, $value2), '0'), 8, '0', STR_PAD_RIGHT));
    }

    /**
     * @dataProvider divisionExamples
     * @test
     */
    public function it_divides_a_value_by_another($value1, $value2, $expected)
    {
        $result = BcMathCalculator::divide($value1, $value2);
        $this->assertEquals(substr($expected, 0, 12), substr($result, 0, 12));
    }

    /**
     * @dataProvider divisionExactExamples
     * @test
     */
    public function it_divides_a_value_by_another_exact($value1, $value2, $expected)
    {
        $this->assertEquals($expected, BcMathCalculator::divide($value1, $value2));
    }

    /**
     * @dataProvider ceilExamples
     * @test
     */
    public function it_ceils_a_value($value, $expected)
    {
        $this->assertEquals($expected, BcMathCalculator::ceil($value));
    }

    /**
     * @dataProvider floorExamples
     * @test
     */
    public function it_floors_a_value($value, $expected)
    {
        $this->assertEquals($expected, BcMathCalculator::floor($value));
    }

    /**
     * @dataProvider absoluteExamples
     * @test
     */
    public function it_calculates_the_absolute_value($value, $expected)
    {
        $this->assertEquals($expected, BcMathCalculator::absolute($value));
    }

    /**
     * @dataProvider roundExamples
     * @test
     */
    public function it_rounds_a_value($value, $mode, $expected)
    {
        $this->assertEquals($expected, BcMathCalculator::round($value, $mode));
    }

    /**
     * @dataProvider compareLessExamples
     * @test
     */
    public function it_compares_values_less($left, $right)
    {
        // Compare with both orders. One must return a value less than zero,
        // the other must return a value greater than zero.
        $this->assertLessThan(0, BcMathCalculator::compare($left, $right));
        $this->assertGreaterThan(0, BcMathCalculator::compare($right, $left));
    }

    /**
     * @dataProvider compareEqualExamples
     * @test
     */
    public function it_compares_values($left, $right)
    {
        // Compare with both orders, both must return zero.
        $this->assertEquals(0, BcMathCalculator::compare($left, $right));
        $this->assertEquals(0, BcMathCalculator::compare($right, $left));
    }

    /**
     * @dataProvider modExamples
     * @test
     */
    public function it_calculates_the_modulus_of_a_value($left, $right, $expected)
    {
        $this->assertEquals($expected, BcMathCalculator::mod($left, $right));
    }

    public function additionExamples()
    {
        return [
            [1, 1, '2'],
            [10, 5, '15'],
        ];
    }

    public function subtractionExamples()
    {
        return [
            [1, 1, '0'],
            [10, 5, '5'],
        ];
    }

    public function multiplicationExamples()
    {
        return [
            [1, 1.5, '1.500000'],
            [10, 1.2500, '12.50000'],
            [100, 0.29, '29.00000'],
            [100, 0.029, '2.900000'],
            [100, 0.0029, '0.290000'],
            [1000, 0.29, '290.0000'],
            [1000, 0.029, '29.00000'],
            [1000, 0.0029, '2.900000'],
            [2000, 0.0029, '5.800000'],
            ['1', 0.006597, '0.006597'],
        ];
    }

    public function divisionExamples()
    {
        return [
            [6, 3, '2.0000000000'],
            [100, 25, '4.0000000000'],
            [2, 4, '0.5000000000'],
            [20, 0.5, '40.0000000000'],
            [2, 0.5, '4.0000000000'],
            [181, 17, '10.64705882352941'],
            [98, 28, '3.5000000000'],
            [98, 25, '3.9200000000'],
            [98, 24, '4.083333333333333'],
            [1, 5.1555, '0.19396760740956'],
            ['-500', 110, '-4.54545454545455'],
        ];
    }

    public function divisionExactExamples()
    {
        return [
            [6, 3, '2.00000000000000'],
            [100, 25, '4.00000000000000'],
            [2, 4, '0.50000000000000'],
            [20, 0.5, '40.00000000000000'],
            [2, 0.5, '4.00000000000000'],
            [98, 28, '3.50000000000000'],
            [98, 25, '3.92000000000000'],
        ];
    }

    public function ceilExamples()
    {
        return [
            [1.2, '2'],
            [-1.2, '-1'],
            [2.00, '2'],
        ];
    }

    public function floorExamples()
    {
        return [
            [2.7, '2'],
            [-2.7, '-3'],
            [2.00, '2'],
        ];
    }

    public function absoluteExamples()
    {
        return [
            [2, '2'],
            [-2, '2'],
        ];
    }

    public function compareLessExamples()
    {
        return [
            [0, 1],
            ['0', '1'],
            ['0.0005', '1'],
            ['0.000000000000000000000000005', '1'],
            ['-1000', '1000', -1],
        ];
    }

    public function compareEqualExamples()
    {
        return [
            [1, 1],
            ['1', '1'],
            ['-1000', '-1000'],
        ];
    }

    public function modExamples()
    {
        return [
            [11, 5, '1'],
            [9, 3, '0'],
            [1006, 10, '6'],
            [1007, 10, '7'],
            [-13, -5, '-3'],
            [-13, 5, '-3'],
            [13, -5, '3'],
        ];
    }
}
