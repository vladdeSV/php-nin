<?php
declare(strict_types=1);

namespace NIN\Parsers;

use DateTimeImmutable;
use InvalidArgumentException;
use NIN\NationalIdentificationNumbers\Denmark\DenmarkPersonalIdentificationNumber;

abstract class DenmarkNationalIdentificationNumberParser
{
    private const REGEX_PERSONAL_IDENTIFICATION_NUMBER = /** @lang PhpRegExp */
        '/^'
        . '(?<DD>\d{2})'
        . '(?<MM>\d{2})'
        . '(?<YY>\d{2})'
        . '-?'
        . '(?<serialNumber>'
        . '' . '(?<centuryDigit>\d)(?<individualNumber>'
        . '' . '' . '(?<uniqueNumbers>\d{2})(?<checksum>\d)'
        . '' . ')'
        . ')'
        . '$/';

    public static function parse(string $personalIdentificationNumber): DenmarkPersonalIdentificationNumber
    {
        $matches = [];
        if (!preg_match(self::REGEX_PERSONAL_IDENTIFICATION_NUMBER, $personalIdentificationNumber, $matches)) {
            throw new InvalidArgumentException("Invalid format '$personalIdentificationNumber'.");
        }

        $YY = (int)$matches['YY'];
        $MM = (int)$matches['MM'];
        $DD = (int)$matches['DD'];
        $centuryDigit = (int)$matches['centuryDigit'];

        $year = self::calculateYearFromTwoDigitYearAndCenturyDigit($YY, $centuryDigit);
        if (!checkdate((int)$MM, (int)$DD, (int)$year)) {
            throw new InvalidArgumentException("Invalid date '{$year}-{$MM}-{$DD}'.");
        }

        $date = DateTimeImmutable::createFromFormat('Y-m-d', "$year-$MM-$DD");

        $serialNumber = (int)$matches['serialNumber'];

        return new DenmarkPersonalIdentificationNumber($date, $serialNumber);
    }

    private static function calculateYearFromTwoDigitYearAndCenturyDigit(int $twoDigitYear, $centuryDigit): int
    {
        $year = null;

        if (self::isNumberInRange($centuryDigit, 0, 3)) {
            $year = 1900 + $twoDigitYear;
        } else if (($centuryDigit === 4) || ($centuryDigit === 9)) {
            if (self::isNumberInRange($twoDigitYear, 0, 36)) {
                $year = 2000 + $twoDigitYear;
            } else if (self::isNumberInRange($twoDigitYear, 37, 99)) {
                $year = 1900 + $twoDigitYear;
            }
        } else if (self::isNumberInRange($centuryDigit, 5, 8)) {
            if (self::isNumberInRange($twoDigitYear, 0, 57)) {
                $year = 2000 + $twoDigitYear;
            } else if (self::isNumberInRange($twoDigitYear, 58, 99)) {
                $year = 1800 + $twoDigitYear;
            }
        }

        return $year;
    }

    private static function isNumberInRange(int $number, int $a, int $b): bool
    {
        return ($number >= $a) && ($number <= $b);
    }
}