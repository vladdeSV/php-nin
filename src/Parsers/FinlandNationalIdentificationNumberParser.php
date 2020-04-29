<?php

declare(strict_types=1);

namespace NIN\Parsers;

use DateTimeImmutable;
use InvalidArgumentException;
use NIN\NationalIdentificationNumbers\Finland\FinlandPersonalIdentificationCode;

abstract class FinlandNationalIdentificationNumberParser
{
    private const REGEX_PERSONAL_IDENTITY_CODE = /** @lang PhpRegExp */
        '/^'
        . '(?<DD>\d{2})'
        . '(?<MM>\d{2})'
        . '(?<YY>\d{2})'
        . '(?<separator>[-+A])'
        . '(?<individualNumber>\d{3})'
        . '(?<checksum>[\dA-Y])'
        . '$/';

    public static function parse(string $nationalIdentificationNumber): FinlandPersonalIdentificationCode
    {
        $matches = [];
        if (!preg_match(self::REGEX_PERSONAL_IDENTITY_CODE, $nationalIdentificationNumber, $matches)) {
            throw new InvalidArgumentException("Invalid format '$nationalIdentificationNumber'.");
        }

        $twoDigitYear = (int)$matches['YY'];
        $month = (int)$matches['MM'];
        $day = (int)$matches['DD'];
        $separator = $matches['separator'];
        $individualNumber = (int)$matches['individualNumber'];
        $checksum = $matches['checksum'];

        if ($individualNumber <= 1) {
            throw new InvalidArgumentException('Invalid individual number.');
        }

        $century = self::getCenturyFromSeparator($separator);
        $year = $century + $twoDigitYear;

        if (!checkdate($month, $day, $year)) {
            throw new InvalidArgumentException("Invalid date. {$year}-{$month}-{$day} does not exist.");
        }

        $date = DateTimeImmutable::createFromFormat('Y-m-d', "$year-$month-$day");

        if (FinlandPersonalIdentificationCode::calculateChecksum($date, $individualNumber) !== $checksum) {
            throw new InvalidArgumentException('Invalid checksum.');
        }

        return new FinlandPersonalIdentificationCode($date, $individualNumber);
    }

    private static function getCenturyFromSeparator($separator): int
    {
        $century = null;
        /** @noinspection PhpSwitchCaseWithoutDefaultBranchInspection RegExp ensures separator is either +, -, or A */
        switch ($separator) {
            case '+':
                $century = 1800;
                break;
            case '-':
                $century = 1900;
                break;
            case 'A':
                $century = 2000;
                break;
        }

        assert($century !== null);

        return $century;
    }
}
