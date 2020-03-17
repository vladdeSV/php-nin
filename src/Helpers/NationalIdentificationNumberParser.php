<?php

declare(strict_types=1);

namespace NIN\Helpers;

use Exception;
use InvalidArgumentException;
use NIN\NationalIdentificationNumbers\NationalIdentificationNumberInterface;
use NIN\NationalIdentificationNumbers\SwedenNationalIdentificationNumber;

final class NationalIdentificationNumberParser
{
    public static function parse(string $nationalIdentificationNumber, string $countryCode)
    {
        if (!isset(self::AVAILABLE_COUNTRY_CODES[$countryCode])) {
            throw new Exception("'$countryCode' is not supported.");
        }

        return (self::AVAILABLE_COUNTRY_CODES[$countryCode])::parse($nationalIdentificationNumber);
    }

    public static function detectCountry(string $nationalIdentificationNumber): ?string
    {
        /** @var string $countryCode */
        /** @var NationalIdentificationNumberInterface $class */
        foreach (self::AVAILABLE_COUNTRY_CODES as $countryCode => $class) {
            try {
                $class::parse($nationalIdentificationNumber);
                return $countryCode;
            } catch (InvalidArgumentException $exception) {
                continue;
            }
        }

        return null;
    }

    private const AVAILABLE_COUNTRY_CODES = [
        'se' => SwedenNationalIdentificationNumber::class
    ];
}
