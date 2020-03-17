# National Identification Numbers library
[![Build status](https://travis-ci.org/vladdeSV/php-nin.png?branch=master)](https://travis-ci.org/vladdeSV/php-nin) 

This library validates National Identification Numbers (NINs).

## Supported countries
* Sweden


## Usage

```php
use NIN\Helpers\NationalIdentificationNumberParser;

$nin = NationalIdentificationNumberParser::tryParse('890629-1870', 'se');

echo $nin;                   // '890629-1870'
echo $nin->getCountryCode(); // 'se'
```


```php
$countryCode = NationalIdentificationNumberParser::detectCountry('890629-1870'); // valid swedish personal identity number

echo $countryCode; // 'se'
```

```php
use NIN\NationalIdentificationNumbers\SwedenNationalIdentificationNumber;

$snin = SwedenNationalIdentificationNumber::parse('951124-3611');

echo $snin;                   // '951124-3611'
echo $snin->getCountryCode(); // 'se'
```

## Installation

```
composer require vladdesv/php-nin
```

## Licence
MIT
