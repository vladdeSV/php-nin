<?php

declare(strict_types=1);

namespace NIN;

use Exception;
use NIN\NationalIdentificationNumbers\FinlandPersonalIdentificationCode;
use NIN\NationalIdentificationNumbers\NationalIdentificationNumberInterface;
use NIN\NationalIdentificationNumbers\NorwayBirthNumber;
use NIN\NationalIdentificationNumbers\SwedenPersonalIdentificationNumber;

final class NationalIdentificationNumberParser
{
    public static function parse(string $nationalIdentificationNumber, string $countryCode): NationalIdentificationNumberInterface
    {
        if (!isset(self::AVAILABLE_COUNTRY_CODES[$countryCode])) {
            throw new Exception("'$countryCode' is not supported.");
        }

        $nin = self::AVAILABLE_COUNTRY_CODES[$countryCode];
        return new $nin($nationalIdentificationNumber);
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
        SwedenPersonalIdentificationNumber::COUNTRY_CODE => SwedenPersonalIdentificationNumber::class,
        NorwayBirthNumber::COUNTRY_CODE => NorwayBirthNumber::class,
        FinlandPersonalIdentificationCode::COUNTRY_CODE => FinlandPersonalIdentificationCode::class,
    ];
}
