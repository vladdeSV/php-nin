<?php

declare(strict_types=1);

namespace NIN\Tests\Helpers;

use NIN\Helpers\CountryCodeHelper;
use PHPUnit\Framework\TestCase;

class CountryCodeHelperTest extends TestCase
{
    /**
     * @dataProvider validCountryCodes
     *
     * @param string $countryCode
     */
    public function testValidCountryCodes(string $countryCode)
    {
        self::assertTrue(CountryCodeHelper::isValidCountryCode($countryCode));
    }

    /**
     * @dataProvider invalidCountryCodes
     *
     * @param string $countryCode
     */
    public function testInvalidCountryCodes(string $countryCode)
    {
        self::assertFalse(CountryCodeHelper::isValidCountryCode($countryCode));
    }

    public function validCountryCodes(): array
    {
        return [
            ['SE'],
            ['FI'],
            ['NO'],
            ['PL'],
            ['GB'],
        ];
    }

    public function invalidCountryCodes(): array
    {
        return [
            ['se'],
            ['xx'],
            ['-'],
            ['P'],
            ['PLL'],
            [''],
            ['XX'],
        ];
    }
}
