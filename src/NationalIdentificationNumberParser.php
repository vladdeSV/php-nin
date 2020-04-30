<?php

declare(strict_types=1);

namespace NIN;

use Exception;
use NIN\Helpers\CountryCodeHelper;
use NIN\NationalIdentificationNumbers\Denmark\DenmarkPersonalIdentificationNumber;
use NIN\NationalIdentificationNumbers\Finland\FinlandPersonalIdentificationCode;
use NIN\NationalIdentificationNumbers\Iceland\IcelandIdentificationNumber;
use NIN\NationalIdentificationNumbers\NationalIdentificationNumberInterface;
use NIN\NationalIdentificationNumbers\Norway\NorwayNationalIdentificationNumber;
use NIN\NationalIdentificationNumbers\Sweden\SwedenNationalIdentificationNumber;
use NIN\Parsers\DenmarkNationalIdentificationNumberParser;
use NIN\Parsers\FinlandNationalIdentificationNumberParser;
use NIN\Parsers\IcelandNationalIdentificationNumberParser;
use NIN\Parsers\NorwayNationalIdentificationNumberParser;
use NIN\Parsers\SwedenNationalIdentificationNumberParser;

final class NationalIdentificationNumberParser
{
    /**
     * @param string $nationalIdentificationNumber
     * @param string $countryCode
     *
     * @throws Exception
     *
     * @return NationalIdentificationNumberInterface
     */
    public static function parse(string $nationalIdentificationNumber, string $countryCode): NationalIdentificationNumberInterface
    {
        if (!CountryCodeHelper::isValidCountryCode($countryCode)) {
            throw new Exception("'$countryCode' is not a valid country code.");
        }

        switch ($countryCode) {
            default:
                throw new Exception("'$countryCode' is not supported.");
            case SwedenNationalIdentificationNumber::COUNTRY_CODE:
                return SwedenNationalIdentificationNumberParser::parse($nationalIdentificationNumber);
            case NorwayNationalIdentificationNumber::COUNTRY_CODE:
                return NorwayNationalIdentificationNumberParser::parse($nationalIdentificationNumber);
            case FinlandPersonalIdentificationCode::COUNTRY_CODE:
                return FinlandNationalIdentificationNumberParser::parse($nationalIdentificationNumber);
            case IcelandIdentificationNumber::COUNTRY_CODE:
                return IcelandNationalIdentificationNumberParser::parse($nationalIdentificationNumber);
            case DenmarkPersonalIdentificationNumber::COUNTRY_CODE:
                return DenmarkNationalIdentificationNumberParser::parse($nationalIdentificationNumber);
        }
    }

    /**
     * @param string $nationalIdentificationNumber
     * @param string $countryCode
     *
     * @return NationalIdentificationNumberInterface|null
     */
    public static function tryParse(string $nationalIdentificationNumber, string $countryCode): ?NationalIdentificationNumberInterface
    {
        try {
            return self::parse($nationalIdentificationNumber, $countryCode);
        } catch (Exception $exception) {
            return null;
        }
    }
}
