# National Identification Numbers library
[![Build status](https://travis-ci.org/vladdeSV/php-nin.png?branch=master)](https://travis-ci.org/vladdeSV/php-nin) 

This library validates National Identification Numbers (NINs).

## Supported countries
* Sweden
* Norway


## Usage

```php
use NIN\NationalIdentificationNumberParser;

$nin = NationalIdentificationNumberParser::tryParse('890629-1870', 'se');

echo $nin;                   // '890629-1870'
echo $nin->getCountryCode(); // 'se'
```

## Installation

```
composer require vladdesv/php-nin
```

## License
MIT
