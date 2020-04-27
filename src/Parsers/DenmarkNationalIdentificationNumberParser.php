<?php

namespace NIN\Parsers;

use DateTimeImmutable;
use InvalidArgumentException;
use NIN\NationalIdentificationNumbers\Denmark\DenmarkPersonalIdentificationNumber;

/**
 * https://da.wikipedia.org/wiki/CPR-nummer
 */
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
            throw new InvalidArgumentException('Invalid format.');
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
        $isCalculateChecksum = $date < (DateTimeImmutable::createFromFormat('Y-m-d', '2007-10-01'));
        if ($isCalculateChecksum) {
            $checksum = (int)$matches['checksum'];
            $calculatedChecksum = self::calculateChecksum($date, $centuryDigit, $matches['uniqueNumbers']); //fixme
            if ($calculatedChecksum !== $checksum) {
                throw new InvalidArgumentException("Invalid checksum.");
            }
        }

        $serialNumber = (int)$matches['serialNumber'];

        return new DenmarkPersonalIdentificationNumber($date, $serialNumber);
    }

    private static function calculateYearFromTwoDigitYearAndCenturyDigit(int $twoDigitYear, $centuryDigit): int
    {
        $year = null;

        if (self::isNumberInRange($centuryDigit, 0, 3)) {

            $year = 1900 + $twoDigitYear;

        } else if ($centuryDigit === 4 || $centuryDigit === 9) {

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
        return $number >= $a && $number <= $b;
    }

    private static function calculateChecksum(DateTimeImmutable $date, int $centuryDigit, int $uniqueNumbers): int
    {
        $s = sprintf("%02d%02d%02d%d%02d", (int)$date->format('d'), (int)$date->format('m'), (int)$date->format('y'), $centuryDigit, $uniqueNumbers);

        $checksum = (int)($s[0]) * 4
            + (int)($s[1]) * 3
            + (int)($s[2]) * 2
            + (int)($s[3]) * 7
            + (int)($s[4]) * 6
            + (int)($s[5]) * 5
            + (int)($s[6]) * 4
            + (int)($s[7]) * 3
            + (int)($s[8]) * 2;

        $checksum = (10 - ($checksum % 10)) % 10;

        return $checksum;
    }
}