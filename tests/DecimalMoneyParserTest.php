<?php

use JKMoney\Parser;
use PHPUnit\Framework\TestCase;

final class DecimalMoneyParserTest extends TestCase
{
    /**
     * @dataProvider formattedMoneyExamples
     * @test
     */
    public function it_parses_money($decimal, $result)
    {
        $this->assertEquals($result, (int) Parser::parse($decimal));
    }

    /**
     * @dataProvider invalidMoneyExamples
     * @test
     */
    public function it_throws_an_exception_upon_invalid_inputs($input)
    {
        $this->expectException(\Exception::class);

        Parser::parse($input);
    }

    public function formattedMoneyExamples()
    {
        return [
            ['1000.50', 100050],
            ['1000.00', 100000],
            ['1000.0', 100000],
            ['1000', 100000],
            ['0.01', 1],
            ['0.00', 0],
            ['1', 100],
            ['-1000.50', -100050],
            ['-1000.00', -100000],
            ['-1000.0', -100000],
            ['-1000', -100000],
            ['-0.01', -1],
            ['-1', -100],
            ['1000.501', 100050],
            ['1000.001', 100000],
            ['1000.50', 100050],
            ['1000.00', 100000],
            ['1000.0', 100000],
            ['1000', 100000],
            ['0.001', 0],
            ['0.01', 1],
            ['1', 100],
            ['-1000.501', -100050],
            ['-1000.001', -100000],
            ['-1000.50', -100050],
            ['-1000.00', -100000],
            ['-1000.0', -100000],
            ['-1000', -100000],
            ['-0.001', 0],
            ['-0.01', -1],
            ['-1', -100],
            ['1000.50', 100050],
            ['1000.00', 100000],
            ['1000.0', 100000],
            ['1000', 100000],
            ['0.01', 1],
            ['1', 100],
            ['-1000.50', -100050],
            ['-1000.00', -100000],
            ['-1000.0', -100000],
            ['-1000', -100000],
            ['-0.01', -1],
            ['-1', -100],
            ['', 0],
            ['.99', 99],
            ['99.', 9900],
            ['-9.999', -1000],
            ['9.999', 1000],
            ['9.99', 999],
            ['-9.99', -999],
        ];
    }

    public static function invalidMoneyExamples()
    {
        return [
            ['INVALID'],
            ['.'],
        ];
    }
}
