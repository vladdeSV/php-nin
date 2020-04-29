<?php

declare(strict_types=1);

namespace NIN\Parsers;

use DateTimeImmutable;
use InvalidArgumentException;
use NIN\NationalIdentificationNumbers\Iceland\IcelandIdentificationNumber;

abstract class IcelandNationalIdentificationNumberParser
{
    private const REGEX_IDENTIFICATION_NUMBER = /** @lang PhpRegExp */
        '/^'
        . '(?<DD>\d{2})'
        . '(?<MM>\d{2})'
        . '(?<YY>\d{2})'
        . '-?'
        . '(?<individualNumber>\d{2})'
        . '(?<checksum>\d)'
        . '(?<centuryDigit>[890])'
        . '$/';

    public static function parse(string $identificationNumber): IcelandIdentificationNumber
    {
        $matches = [];
        if (!preg_match(self::REGEX_IDENTIFICATION_NUMBER, $identificationNumber, $matches)) {
            throw new InvalidArgumentException('Invalid format.');
        }

        $YY = (int)$matches['YY'];
        $MM = (int)$matches['MM'];
        $DD = (int)$matches['DD'];
        $individualNumber = (int)$matches['individualNumber'];
        $checksum = (int)$matches['checksum'];
        $centuryDigit = (int)$matches['centuryDigit'];

        $year = self::getCenturyFromCenturyDigit($centuryDigit) + $YY;

        if (!checkdate($MM, $DD, $year)) {
            throw new InvalidArgumentException('Invalid date.');
        }

        $date = DateTimeImmutable::createFromFormat('Y-m-d', "$year-$MM-$DD");

        if ($checksum !== IcelandIdentificationNumber::calculateChecksum($date, $individualNumber)) {
            throw new InvalidArgumentException('Invalid checksum.');
        }

        return new IcelandIdentificationNumber($date, $individualNumber);
    }

    private static function getCenturyFromCenturyDigit(int $centuryDigit): int
    {
        $century = null;

        if ($centuryDigit === 8) {
            $century = 1800;
        } else {
            if ($centuryDigit === 9) {
                $century = 1900;
            } else {
                if ($centuryDigit === 0) {
                    $century = 2000;
                }
            }
        }

        return $century;
    }
}
