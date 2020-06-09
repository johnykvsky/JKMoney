<?php

use JKMoney\Money;

trait AggregateExamples
{
    public function sumExamples()
    {
        return [
            [[Money::create(5), Money::create(10), Money::create(15)], Money::create(30)],
            [[Money::create(-5), Money::create(-10), Money::create(-15)], Money::create(-30)],
            [[Money::create(0)], Money::create(0)],
        ];
    }

    public function minExamples()
    {
        return [
            [[Money::create(5), Money::create(10), Money::create(15)], Money::create(5)],
            [[Money::create(-5), Money::create(-10), Money::create(-15)], Money::create(-15)],
            [[Money::create(0)], Money::create(0)],
        ];
    }

    public function maxExamples()
    {
        return [
            [[Money::create(5), Money::create(10), Money::create(15)], Money::create(15)],
            [[Money::create(-5), Money::create(-10), Money::create(-15)], Money::create(-5)],
            [[Money::create(0)], Money::create(0)],
        ];
    }

    public function avgExamples()
    {
        return [
            [[Money::create(5), Money::create(10), Money::create(15)], Money::create(10)],
            [[Money::create(-5), Money::create(-10), Money::create(-15)], Money::create(-10)],
            [[Money::create(0)], Money::create(0)],
        ];
    }
}
