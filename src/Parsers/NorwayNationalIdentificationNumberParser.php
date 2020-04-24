<?php

namespace NIN\Parsers;

use DateTimeImmutable;
use InvalidArgumentException;
use NIN\NationalIdentificationNumbers\Norway\NorwayBirthNumber;
use NIN\NationalIdentificationNumbers\Norway\NorwayDNumber;
use NIN\NationalIdentificationNumbers\Norway\NorwayHNumber;
use NIN\NationalIdentificationNumbers\Norway\NorwayNationalIdentificationNumber;

abstract class NorwayNationalIdentificationNumberParser
{
    private const REGEX_BIRTH_NUMBER = /** @lang PhpRegExp */
        '/^(?<DD>\d{2})(?<MM>\d{2})(?<YY>\d{2})(?<individualNumber>\d{3})(?<checksum>\d{2})$/';

    public static function parse(string $nationalIdentificationNumber): NorwayNationalIdentificationNumber
    {
        $matches = [];
        if (!preg_match(self::REGEX_BIRTH_NUMBER, $nationalIdentificationNumber, $matches)) {
            throw new InvalidArgumentException('Invalid format.');
        }

        $YY = (int)$matches['YY'];
        $MM = (int)$matches['MM'];
        $DD = (int)$matches['DD'];
        $individualNumber = (int)$matches['individualNumber'];
        $checksum = (int)$matches['checksum'];

        $isDNumber = $DD >= 41 && $DD <= 71;
        if ($isDNumber) {
            $DD -= 40;
        }

        $isHNumber = $MM >= 41 && $MM <= 52;
        if ($isHNumber) {
            $MM -= 40;
        }

        if ($isDNumber && $isHNumber) {
            throw new InvalidArgumentException("Cannot be both D-number and H-number.");
        }

        $year = self::getYearFromIndividualNumberAndTwoDigitYear($individualNumber, $YY);

        if (!checkdate((int)$MM, (int)$DD, (int)$year)) {
            throw new InvalidArgumentException("Invalid date '{$year}-{$MM}-{$DD}'.");
        }

        $date = DateTimeImmutable::createFromFormat('Y-m-d', "$year-$MM-$DD");

        $calculatedChecksum = self::calculateChecksumFromDateAndIndividualNumber($date, $individualNumber, $isDNumber, $isHNumber);
        if ($calculatedChecksum !== $checksum) {
            throw new InvalidArgumentException("Invalid checksum. Expected '$calculatedChecksum', got '$checksum'.");
        }

        if ($isDNumber) {
            return new NorwayDNumber($date, $individualNumber);
        } else if ($isHNumber) {
            return new NorwayHNumber($date, $individualNumber);
        }

        return new NorwayBirthNumber($date, $individualNumber);
    }

    public static function calculateChecksumFromDateAndIndividualNumber(DateTimeImmutable $date, int $individualNumber, bool $isDNumber, bool $isHNumber): int
    {
        $day = (int)$date->format('d') + ($isDNumber ? 40 : 0);
        $month = (int)$date->format('m') + ($isHNumber ? 40 : 0);
        $numbers = sprintf('%02d%02d%02d%03d', $day, $month, (int)$date->format('y'), $individualNumber);

        $k1 = 11 - ((3 * $numbers[0] + 7 * $numbers[1] + 6 * $numbers[2] + 1 * $numbers[3] + 8 * $numbers[4] + 9 * $numbers[5] + 4 * $numbers[6] + 5 * $numbers[7] + 2 * $numbers[8]) % 11);
        $k2 = 11 - ((5 * $numbers[0] + 4 * $numbers[1] + 3 * $numbers[2] + 2 * $numbers[3] + 7 * $numbers[4] + 6 * $numbers[5] + 5 * $numbers[6] + 4 * $numbers[7] + 3 * $numbers[8] + 2 * $k1) % 11);

        $str = $k1 . $k2;
        return (int)$str;
    }

    private static function getYearFromIndividualNumberAndTwoDigitYear(int $individualNumber, int $twoDigitYear)
    {
        /*
         * 500–749 indiviual number means between 1854–1899 (54-99)
         * 900–999 indiviual number means between 1940–1999 (40-99)
         * 000-499 indiviual number means between 1900–1999 (00-99)
         * 500–999 indiviual number means between 2000–2039 (00-39)
         */

        $year = null;

        if (self::isNumberInRange($individualNumber, 500, 749) && self::isNumberInRange($twoDigitYear, 54, 99)) {
            $year = (int)("18{$twoDigitYear}");
        } else if (self::isNumberInRange($individualNumber, 499, 999) && self::isNumberInRange($twoDigitYear, 40, 99)) {
            // special case for people born between 1940 -> 1999, span also includes 900-999
            $year = (int)("19{$twoDigitYear}");
        } else if (self::isNumberInRange($individualNumber, 0, 499) && self::isNumberInRange($twoDigitYear, 00, 99)) {
            $year = (int)("19{$twoDigitYear}");
        } else if (self::isNumberInRange($individualNumber, 500, 999) && self::isNumberInRange($twoDigitYear, 00, 39)) {
            $year = (int)("20{$twoDigitYear}");
        }

        return $year;
    }

    private static function isNumberInRange(int $number, int $a, int $b): bool
    {
        return $number >= $a && $number <= $b;
    }
}