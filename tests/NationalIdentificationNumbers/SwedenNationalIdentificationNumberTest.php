<?php

declare(strict_types=1);

namespace NationalIdentificationNumber\Tests\NationalIdentificationNumbers;

use InvalidArgumentException;
use NationalIdentificationNumber\NationalIdentificationNumbers\SwedenNationalIdentificationNumber;
use PHPUnit\Framework\TestCase;

class SwedenNationalIdentificationNumberTest extends TestCase
{
    /**
     * @dataProvider validPersonalIdentityNumbers
     * @param string $validPersonalIdentityNumber
     */
    public function testValid(string $validPersonalIdentityNumber)
    {
        self::assertNotNull(SwedenNationalIdentificationNumber::parse($validPersonalIdentityNumber));
    }

    /**
     * @dataProvider invalidPersonalIdentityNumbers
     * @param string $invalidPersonalIdentityNumber
     */
    public function testInvalid(string $invalidPersonalIdentityNumber)
    {
        self::expectException(InvalidArgumentException::class);

        SwedenNationalIdentificationNumber::parse($invalidPersonalIdentityNumber);
    }

    /**
     * @dataProvider validPersonalIdentityNumbers
     * @param string $validPersonalIdentityNumber
     */
    public function testToString(string $validPersonalIdentityNumber)
    {
        self::assertSame(
            $validPersonalIdentityNumber,
            SwedenNationalIdentificationNumber::parse($validPersonalIdentityNumber)->__toString()
        );
    }

    public function testCountryCode()
    {
        $snin = SwedenNationalIdentificationNumber::parse('770523-7100');
        self::assertNotNull($snin);

        self::assertSame('se', $snin->getCountryCode());
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
        ];
    }

    public function invalidPersonalIdentityNumbers(): array
    {
        return [
            ['abc'],
            ['1902282258'], // missing separator
            ['123456-7890'],
            ['190228-4048'], // valid date, incorrect checksum
            ['190229-4048'], // correct checksum, invalid date
        ];
    }
}
