<?php

declare(strict_types=1);

namespace NIN\Tests\Parsers;

use InvalidArgumentException;
use NIN\NationalIdentificationNumbers\Denmark\DenmarkPersonalIdentificationNumber;
use NIN\Parsers\DenmarkNationalIdentificationNumberParser;
use PHPUnit\Framework\TestCase;

class DenmarkNationalIdentificationNumberParserTest extends TestCase
{
    /**
     * @dataProvider validPersonalIdentityCodes
     * @param string $personalIdentificationNumber
     */
    public function testValidPersonalIdentityCode(string $personalIdentificationNumber)
    {
        self::assertNotNull(DenmarkNationalIdentificationNumberParser::parse($personalIdentificationNumber));
    }

    /**
     * @dataProvider invalidPersonalIdentityCodes
     * @param string $personalIdentificationNumber
     */
    public function testInvalidPersonalIdentityCode($personalIdentificationNumber)
    {
        self::expectException(InvalidArgumentException::class);

        DenmarkNationalIdentificationNumberParser::parse($personalIdentificationNumber);
    }

    /**
     * @dataProvider validPersonalIdentityCodes
     * @param string $personalIdentificationNumber
     */
    public function testToString(string $personalIdentificationNumber)
    {
        $dnin = DenmarkNationalIdentificationNumberParser::parse($personalIdentificationNumber);
        self::assertNotNull($dnin);
        self::assertSame($personalIdentificationNumber, $dnin->__toString());
    }

    /**
     * @dataProvider validPersonalIdentityCodes
     * @param string $personalIdentificationNumber
     */
    public function testGetCountryCode(string $personalIdentificationNumber)
    {
        $dnin = DenmarkNationalIdentificationNumberParser::parse($personalIdentificationNumber);
        self::assertSame(DenmarkPersonalIdentificationNumber::COUNTRY_CODE, $dnin->getCountryCode());
    }

    public function validPersonalIdentityCodes(): array
    {
        return [
            ['211062-5629'],
            ['011007-5111'], // after sept. 30th, invalid checksum
        ];
    }

    public function invalidPersonalIdentityCodes(): array
    {
        return [
            [''],
            ['abc123'],
            ['211062-5628'], // invalid checksum
            ['300262-5629'], // invalid date
        ];
    }
}
