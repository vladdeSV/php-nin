# National Identification Numbers library
[![php version](https://img.shields.io/badge/php-%3E%3D7.2-8892BF.svg?logo=php)](https://github.com/vladdeSV/php-nin/blob/develop/composer.json)
[![develop](https://github.com/vladdeSV/php-nin/workflows/develop/badge.svg?branch=develop)](https://github.com/vladdeSV/php-nin/actions?query=workflow%3Adevelop)

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
// no stable version released yet
composer require vladdesv/php-nin
```

## License
MIT
