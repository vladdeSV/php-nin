![logo](https://github.com/vladdeSV/php-nin/raw/develop/resources/logo-transparent.png)

# National Identification Numbers
[![php version](https://img.shields.io/badge/php-%3E%3D7.2-8892BF.svg?logo=php)](https://github.com/vladdeSV/php-nin/blob/develop/composer.json)
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
