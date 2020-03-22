<?php

declare(strict_types=1);

namespace NIN;

use Exception;
use InvalidArgumentException;
use NIN\NationalIdentificationNumbers\NationalIdentificationNumberInterface;
use NIN\NationalIdentificationNumbers\SwedenNationalIdentificationNumber;

final class NationalIdentificationNumberParser
{
    public static function parse(string $nationalIdentificationNumber, string $countryCode): NationalIdentificationNumberInterface
    {
        if (!isset(self::AVAILABLE_COUNTRY_CODES[$countryCode])) {
            throw new Exception("'$countryCode' is not supported.");
        }

        return (self::AVAILABLE_COUNTRY_CODES[$countryCode])::parse($nationalIdentificationNumber);
    }

    public static function tryParse(string $nationalIdentificationNumber, string $countryCode): ?NationalIdentificationNumberInterface
    {
        try {
            return self::parse($nationalIdentificationNumber, $countryCode);
        } catch (Exception $exception) {
            return null;
        }
    }

    private const AVAILABLE_COUNTRY_CODES = [
        'se' => SwedenNationalIdentificationNumber::class
    ];
}
