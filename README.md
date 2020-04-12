# National Identification Numbers library
[![Build status](https://travis-ci.org/vladdeSV/php-nin.png?branch=master)](https://travis-ci.org/vladdeSV/php-nin) 
[![build](https://github.com/vladdeSV/php-nin/workflows/build/badge.svg)](https://github.com/vladdeSV/php-nin/actions?query=workflow%3Abuild)

This library validates the structure of identification numbers.

## Supported countries
* Sweden
  * Personal identification numbers
  * Coordination numbers
* Norway
  * Birth numbers
  * D-numbers
  * H-numbers
* Finland
  * Personal identity code
* Iceland
  * Identification number

## Usage

```php
use NIN\NationalIdentificationNumberParser;

$nin = NationalIdentificationNumberParser::tryParse('890629-1870', 'SE');

echo $nin;                   // '890629-1870'
echo $nin->getCountryCode(); // 'SE'
```

## Installation

```
composer require vladdesv/php-nin
```

## License
MIT
