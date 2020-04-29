<?php

declare(strict_types=1);

namespace NIN\Tests\Parsers;

use InvalidArgumentException;
use NIN\NationalIdentificationNumbers\Iceland\IcelandIdentificationNumber;
use NIN\Parsers\IcelandNationalIdentificationNumberParser;
use PHPUnit\Framework\TestCase;

class IcelandNationalIdentificationNumberParserTest extends TestCase
{
    /**
     * @dataProvider validPersonalIdentityCodes
     *
     * @param string $identificationNumber
     */
    public function testValidPersonalIdentityCode(string $identificationNumber)
    {
        self::assertNotNull(IcelandNationalIdentificationNumberParser::parse($identificationNumber));
    }

    /**
     * @dataProvider invalidPersonalIdentityCodes
     *
     * @param string $identificationNumber
     */
    public function testInvalidPersonalIdentityCode($identificationNumber)
    {
        self::expectException(InvalidArgumentException::class);

        IcelandNationalIdentificationNumberParser::parse($identificationNumber);
    }

    /**
     * @dataProvider validPersonalIdentityCodes
     *
     * @param string $identificationNumber
     */
    public function testToString(string $identificationNumber)
    {
        $inin = IcelandNationalIdentificationNumberParser::parse($identificationNumber);
        self::assertNotNull($inin);
        self::assertSame($identificationNumber, $inin->__toString());
    }

    /**
     * @dataProvider validPersonalIdentityCodes
     *
     * @param string $identificationNumber
     */
    public function testGetCountryCode(string $identificationNumber)
    {
        $inin = IcelandNationalIdentificationNumberParser::parse($identificationNumber);
        self::assertSame(IcelandIdentificationNumber::COUNTRY_CODE, $inin->getCountryCode());
    }

    public function testAllowOptionalDash()
    {
        self::assertNotNull(IcelandNationalIdentificationNumberParser::parse('120160-3389'));
    }

    public function validPersonalIdentityCodes(): array
    {
        return [
            ['1201603389'],
            ['1201603388'],
            ['1201603380'],
        ];
    }

    public function invalidPersonalIdentityCodes(): array
    {
        return [
            ['4001603389'], // invalid date
            ['1201603386'], // invalid century
            ['1201603319'], // invalid checksum
            [''],
        ];
    }
}
