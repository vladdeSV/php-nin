<?php

declare(strict_types=1);

namespace NIN\Parsers;

use DateTimeImmutable;
use InvalidArgumentException;
use NIN\NationalIdentificationNumbers\Sweden\SwedenCoordinationIdentificationNumber;
use NIN\NationalIdentificationNumbers\Sweden\SwedenPersonalIdentificationNumber;
use NIN\NationalIdentificationNumbers\Sweden\SwedenNationalIdentificationNumber;

abstract class SwedenNationalIdentificationNumberParser
{
    private const REGEX_PERSONAL_IDENTITY_NUMBER = /** @lang PhpRegExp */
        '/^(?<YY>\d{2})'
        . '(?<MM>\d{2})'
        . '(?<DD>\d{2})'
        . '(?<separator>[-+])'
        . '(?<individualNumber>\d{3})'
        . '(?<checksum>\d)$/';

    private const REGEX_PERSONAL_IDENTITY_NUMBER_FULL = /** @lang PhpRegExp */
        '/^(?<YYYY>\d{4})'
        . '(?<MM>\d{2})'
        . '(?<DD>\d{2})'
        . '(?<individualNumber>\d{3})'
        . '(?<checksum>\d)$/';

    public static final function parse(string $personalIdentificationNumber): SwedenNationalIdentificationNumber
    {
        $matches = [];

        $year = null;
        if (preg_match(self::REGEX_PERSONAL_IDENTITY_NUMBER, $personalIdentificationNumber, $matches)) {
            $twoDigitYear = (int)$matches['YY'];
            $separator = $matches['separator'];
            $year = self::calculateYearFromTwoDigitYearAndSeparator($twoDigitYear, $separator);
        } else if (preg_match(self::REGEX_PERSONAL_IDENTITY_NUMBER_FULL, $personalIdentificationNumber, $matches)) {
            $year = (int)$matches['YYYY'];
        } else {
            throw new InvalidArgumentException("Invalid format '$personalIdentificationNumber'.");
        }

        $month = (int)$matches['MM'];
        $day = (int)$matches['DD'];
        $individualNumber = (int)$matches['individualNumber'];
        $checksum = (int)$matches['checksum'];

        $isCoordinationNumber = self::isCoordinationNumberByDay($day);
        if ($isCoordinationNumber) {
            $day -= 60;
        }

        if (!checkdate($month, $day, $year)) {
            throw new InvalidArgumentException("Invalid date '{$year}-{$month}-{$day}'.");
        }

        $date = DateTimeImmutable::createFromFormat('Y-m-d', "$year-$month-$day");

        $calculatedChecksum = self::calculateChecksumFromDateTimeAndIndividualNumber($date, $individualNumber, $isCoordinationNumber);
        if ($calculatedChecksum !== $checksum) {
            throw new InvalidArgumentException("Invalid personal identity number '$personalIdentificationNumber'. Expected checksum '$calculatedChecksum', got '$checksum'");
        }

        if ($isCoordinationNumber) {
            return new SwedenCoordinationIdentificationNumber($date, $individualNumber);
        }

        return new SwedenPersonalIdentificationNumber($date, $individualNumber);
    }

    private static function calculateYearFromTwoDigitYearAndSeparator(int $twoDigitYear, string $separator): int
    {
        $currentYear = (int)(new DateTimeImmutable())->format('Y');
        $year = ((int)(($currentYear - $twoDigitYear) / 100)) * 100 + $twoDigitYear;
        if ($separator === '+') {
            $year -= 100;
        }

        return $year;
    }

    private static function isCoordinationNumberByDay(int $day): bool
    {
        return $day > 60 && $day <= (31 + 60);
    }

    public static function calculateChecksumFromDateTimeAndIndividualNumber(DateTimeImmutable $dateTime, int $individualNumber, bool $isCoordinationNumber)
    {
        $day = (int)$dateTime->format('d') + ($isCoordinationNumber ? 60 : 0);
        $nin = sprintf('%02d%02d%02d%03d', (int)$dateTime->format('y'), (int)$dateTime->format('m'), $day, $individualNumber);

        $numbers = array_map(function ($number) {
            return (int)$number;
        }, str_split($nin));

        $modifiedNumbers = '';
        foreach ($numbers as $index => $number) {
            $multiplier = ($index % 2 === 0) ? 2 : 1;

            $modifiedNumbers .= ($number * $multiplier);
        }

        $numbers = array_map(function ($number) {
            return (int)$number;
        }, str_split($modifiedNumbers));

        // source: https://sv.wikipedia.org/wiki/Personnummer_i_Sverige#Kontrollsiffran
        return (10 - (array_sum($numbers) % 10)) % 10;
    }
}