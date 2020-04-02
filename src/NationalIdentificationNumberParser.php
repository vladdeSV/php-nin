<?php

declare(strict_types=1);

namespace NIN;

use Exception;
use NIN\Helpers\CountryCodeHelper;
use NIN\NationalIdentificationNumbers\FinlandPersonalIdentificationCode;
use NIN\NationalIdentificationNumbers\NationalIdentificationNumberInterface;
use NIN\NationalIdentificationNumbers\NorwayBirthNumber;
use NIN\NationalIdentificationNumbers\SwedenPersonalIdentificationNumber;

final class NationalIdentificationNumberParser
{
    public static function parse(string $nationalIdentificationNumber, string $countryCode): NationalIdentificationNumberInterface
    {
        if (!CountryCodeHelper::isValidCountryCode($countryCode)) {
            throw new Exception("'$countryCode' is not a valid country code.");
        }

        switch ($countryCode) {
            default:
                throw new Exception("'$countryCode' is not supported.");
            case SwedenPersonalIdentificationNumber::COUNTRY_CODE:
                return new SwedenPersonalIdentificationNumber($nationalIdentificationNumber);
            case NorwayBirthNumber::COUNTRY_CODE:
                return new NorwayBirthNumber($nationalIdentificationNumber);
            case FinlandPersonalIdentificationCode::COUNTRY_CODE:
                return new FinlandPersonalIdentificationCode($nationalIdentificationNumber);
        }
    }

    public static function tryParse(string $nationalIdentificationNumber, string $countryCode): ?NationalIdentificationNumberInterface
    {
        try {
            return self::parse($nationalIdentificationNumber, $countryCode);
        } catch (Exception $exception) {
            return null;
        }
    }

}
