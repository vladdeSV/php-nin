<?php

declare(strict_types=1);

namespace NIN\Parsers;

use DateTimeImmutable;
use InvalidArgumentException;
use NIN\NationalIdentificationNumbers\Norway\NorwayBirthNumber;
use NIN\NationalIdentificationNumbers\Norway\NorwayDNumber;
use NIN\NationalIdentificationNumbers\Norway\NorwayHNumber;
use NIN\NationalIdentificationNumbers\Norway\NorwayNationalIdentificationNumber;

final class NorwayNationalIdentificationNumberParser
{
    private const REGEX_BIRTH_NUMBER = /** @lang PhpRegExp */
        '/^'
        . '(?<DD>\d{2})'
        . '(?<MM>\d{2})'
        . '(?<YY>\d{2})'
        . '(?<individualNumber>\d{3})'
        . '(?<checksum>\d{2})'
        . '$/';

    /** @noinspection DuplicatedCode */
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

        $isDNumber = ($DD >= 41) && ($DD <= 71);
        if ($isDNumber) {
            $DD -= 40;
        }

        $isHNumber = ($MM >= 41) && ($MM <= 52);
        if ($isHNumber) {
            $MM -= 40;
        }

        if ($isDNumber && $isHNumber) {
            throw new InvalidArgumentException('Cannot be both D-number and H-number.');
        }

        $year = self::getYearFromIndividualNumberAndTwoDigitYear($individualNumber, $YY);

        if (!checkdate((int)$MM, (int)$DD, (int)$year)) {
            throw new InvalidArgumentException("Invalid date '{$year}-{$MM}-{$DD}'.");
        }

        $date = DateTimeImmutable::createFromFormat('Y-m-d', "$year-$MM-$DD");

        $calculatedChecksum = NorwayNationalIdentificationNumber::calculateChecksumFromDateAndIndividualNumber($date, $individualNumber, $isDNumber, $isHNumber);
        if ($calculatedChecksum !== $checksum) {
            throw new InvalidArgumentException("Invalid checksum. Expected '$calculatedChecksum', got '$checksum'.");
        }

        if ($isDNumber) {
            return new NorwayDNumber($date, $individualNumber);
        } else {
            if ($isHNumber) {
                return new NorwayHNumber($date, $individualNumber);
            }
        }

        return new NorwayBirthNumber($date, $individualNumber);
    }

    private static function getYearFromIndividualNumberAndTwoDigitYear(int $individualNumber, int $twoDigitYear): int
    {
        /*
         * 500–749 indiviual number means between 1854–1899 (54-99)
         * 900–999 indiviual number means between 1940–1999 (40-99)
         * 000-499 indiviual number means between 1900–1999 (00-99)
         * 500–999 indiviual number means between 2000–2039 (00-39)
         */

        $year = null;

        if (self::isNumberInRange($individualNumber, 500, 749) && self::isNumberInRange($twoDigitYear, 54, 99)) {
            $year = (int)"18{$twoDigitYear}";
        } else {
            if (self::isNumberInRange($individualNumber, 499, 999) && self::isNumberInRange($twoDigitYear, 40, 99)) {
                // special case for people born between 1940 -> 1999, span also includes 900-999
                $year = (int)"19{$twoDigitYear}";
            } else {
                if (self::isNumberInRange($individualNumber, 0, 499) && self::isNumberInRange($twoDigitYear, 00, 99)) {
                    $year = (int)"19{$twoDigitYear}";
                } else {
                    if (self::isNumberInRange($individualNumber, 500, 999) && self::isNumberInRange($twoDigitYear, 00, 39)) {
                        $year = (int)"20{$twoDigitYear}";
                    }
                }
            }
        }

        return $year;
    }

    private static function isNumberInRange(int $number, int $a, int $b): bool
    {
        return ($number >= $a) && ($number <= $b);
    }
}
