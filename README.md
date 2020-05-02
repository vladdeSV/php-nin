![logo](https://github.com/vladdeSV/php-nin/raw/develop/resources/logo-transparent.png)

# National Identification Numbers
[![php version](https://img.shields.io/packagist/php-v/vladdesv/php-nin/1.0.0?color=8892BF&logo=php)](https://github.com/vladdeSV/php-nin/blob/develop/composer.json)
[![latest release](https://img.shields.io/packagist/v/vladdeSV/php-nin.svg?style=flat)](https://packagist.org/packages/vladdeSV/php-nin)
[![codecov](https://codecov.io/gh/vladdeSV/php-nin/branch/release/graph/badge.svg)](https://codecov.io/gh/vladdeSV/php-nin)
[![styleci](https://github.styleci.io/repos/247304996/shield?style=flat&branch=release)](https://github.styleci.io/repos/247304996)

This library validates the structure of individual identification numbers. Currently supporting all Nordic countries.

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
* Denmark
  * Personal identification number

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
MIT © [Vladimirs Nordholm](https://github.com/vladdeSV)
