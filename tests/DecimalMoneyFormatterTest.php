<?php

use JKMoney\Formatter;
use JKMoney\Money;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

final class DecimalMoneyFormatterTest extends TestCase
{
    /**
     * @dataProvider moneyExamples
     * @test
     */
    public function it_formats_money($amount, $result)
    {
        $money = new Money($amount);
        $this->assertSame($result, Formatter::format($money->getAmount()));
    }

    public static function moneyExamples()
    {
        return [
            [5005, '50.05'],
            [100, '1.00'],
            [41, '0.41'],
            [5, '0.05'],
            [50, '0.50'],
            [350, '3.50'],
            [1357, '13.57'],
            [61351, '613.51'],
            [-61351, '-613.51'],
            [-6152, '-61.52'],
            [5, '0.05'],
            [50, '0.50'],
            [500, '5.00'],
            [-5055, '-50.55'],
            [5, '0.05'],
            [50, '0.50'],
            [500, '5.00'],
            [-5055, '-50.55'],
            [50050050, '500500.50'],
        ];
    }
}
