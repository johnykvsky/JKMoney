# JKMoney

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

Simplified MoneyPHP: 

 - no currency
 - only BcMath calculator
 - just decimal format
 - dot as separator
 - same base classes and unit tests
 - added shortcut for calculating tax

## Install

Via Composer

``` bash
$ composer require johnykvsky/jkmoney
```

## Usage

Please, look at https://github.com/moneyphp/money for more details - just keep in mind: no currency, only decimal format.

 - Remember: Money class is immutable.
 - Remember: If you create it from integer, you need to pass value in smallest currency items (ie. cents). 
 - Remember: Input can contain only digits and optional dot. No commas, no spaces, no other symbols.

``` php
use JKMoney\Money;
$money = Money::create('50013'); //amount is 50013, internally '5001300'
$money1 = Money::create(50013); //amount is 500.13, internally '50013'
$money2 = Money::create(55.65); //amount is 55.65, internally '5565'
$money3 = Money::create('15.25'); //amount is 15.25, internally '1525'
$result = $money1->add($money2); //amount 555.78
$result = $result->subtract($money3); //amount 540.53
$result = $result->multiply(2); //amount 1081.06
$result = $result->divide(3); //amount 360.35
//getting amount
echo $result->getAmount(); // internal '36035'
echo $result->getValue(); // internal as integer 36035
echo $result->getFormatted(); // for display '360.35', to use in Twig like {{ money.formatted }}
//calculate tax, 23%
echo $result->tax(23)->getValue(); // amount 82.88
echo $result->getValue(); // amount is still 360.35, since Money is immutable
//validate input
$valid = Money::isValid('10.0'); // true
$valid = Money::isValid('10,0'); // false
$valid = Money::isValid('1 500'); // false
$valid = Money::isValid('1,000.0'); // false
```

## Testing

``` bash
$ composer test
```

## Code checking

``` bash
$ composer phpstan
$ composer phpstan-max
```

## Security

If you discover any security related issues, please email johnykvsky@protonmail.com instead of using the issue tracker.

## Credits

- [johnykvsky][link-author]
- [moneyphp][link-moneyphp]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/johnykvsky/JKMoney.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/johnykvsky/JKMoney/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/johnykvsky/JKMoney.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/johnykvsky/JKMoney.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/johnykvsky/JKMoney.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/johnykvsky/JKMoney
[link-travis]: https://travis-ci.org/johnykvsky/JKMoney
[link-scrutinizer]: https://scrutinizer-ci.com/g/johnykvsky/JKMoney/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/johnykvsky/JKMoney
[link-downloads]: https://packagist.org/packages/johnykvsky/JKMoney
[link-author]: https://github.com/johnykvsky
[link-moneyphp]: http://moneyphp.org
