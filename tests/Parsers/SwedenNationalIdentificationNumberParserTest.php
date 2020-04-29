<?php

declare(strict_types=1);

namespace NIN\Tests\Parsers;

use InvalidArgumentException;
use NIN\NationalIdentificationNumbers\Sweden\SwedenNationalIdentificationNumber;
use NIN\Parsers\SwedenNationalIdentificationNumberParser;
use PHPUnit\Framework\TestCase;

class SwedenNationalIdentificationNumberParserTest extends TestCase
{
    /**
     * @dataProvider validPersonalIdentityNumbers
     *
     * @param string $personalIdentityNumber
     */
    public function testValid(string $personalIdentityNumber)
    {
        self::assertNotNull(SwedenNationalIdentificationNumberParser::parse($personalIdentityNumber));
    }

    /**
     * @dataProvider validFullPersonalIdentityNumbers
     *
     * @param string $personalIdentityNumber
     */
    public function testValidFullLength(string $personalIdentityNumber)
    {
        self::assertNotNull(SwedenNationalIdentificationNumberParser::parse($personalIdentityNumber));
    }

    /**
     * @dataProvider invalidPersonalIdentityNumbers
     *
     * @param string $personalIdentityNumber
     */
    public function testInvalid(string $personalIdentityNumber)
    {
        self::expectException(InvalidArgumentException::class);

        SwedenNationalIdentificationNumberParser::parse($personalIdentityNumber);
    }

    /**
     * @dataProvider validPersonalIdentityNumbers
     *
     * @param string $personalIdentityNumber
     */
    public function testToString(string $personalIdentityNumber)
    {
        self::assertSame(
            $personalIdentityNumber,
            SwedenNationalIdentificationNumberParser::parse($personalIdentityNumber)->__toString()
        );
    }

    /**
     * @dataProvider validFullPersonalIdentityNumbers
     *
     * @param string $personalIdentityNumber12
     * @param string $personalIdentityNumber10
     */
    public function testFullLengthToString(string $personalIdentityNumber12, string $personalIdentityNumber10)
    {
        $snin = SwedenNationalIdentificationNumberParser::parse($personalIdentityNumber12);

        self::assertSame(
            $personalIdentityNumber10,
            ($snin)->__toString()
        );
    }

    /**
     * @dataProvider validPersonalIdentityNumbers
     *
     * @param string $personalIdentityNumber
     */
    public function testCountryCode(string $personalIdentityNumber)
    {
        $snin = SwedenNationalIdentificationNumberParser::parse($personalIdentityNumber);

        self::assertSame(SwedenNationalIdentificationNumber::COUNTRY_CODE, $snin->getCountryCode());
    }

    public function validPersonalIdentityNumbers(): array
    {
        return [
            ['190228-2258'],
            ['190228+2258'],
            ['730512-4609'],
            ['180910+4068'],
            ['690628-3384'],
            ['200314+4355'],
            ['200314-4355'],
            ['200374-4352'],
            ['180970+4065'],
            ['690688-3381'],
        ];
    }

    /**
     * Each item is in format [12-digit, 10-digit].
     *
     * @return array
     */
    public function validFullPersonalIdentityNumbers()
    {
        return [
            ['201902282258', '190228-2258'],
            ['191902282258', '190228+2258'],
            ['197305124609', '730512-4609'],
            ['191809104068', '180910+4068'],
            ['196906283384', '690628-3384'],
            ['192003144355', '200314+4355'],
            ['202003144355', '200314-4355'],
        ];
    }

    public function invalidPersonalIdentityNumbers(): array
    {
        return [
            [''],
            ['abc'],
            ['1902282258'], // missing separator
            ['123456-7890'],
            ['190228-4048'], // valid date, incorrect checksum
            ['190229-4048'], // correct checksum, invalid date
            ['201902294048'],
        ];
    }
}
