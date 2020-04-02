<?php

declare(strict_types=1);

namespace NIN\Tests\NationalIdentificationNumbers;

use InvalidArgumentException;
use NIN\NationalIdentificationNumbers\SwedenPersonalIdentificationNumber;
use PHPUnit\Framework\TestCase;

class SwedenPersonalIdentificationNumberTest extends TestCase
{
    /**
     * @dataProvider validPersonalIdentityNumbers
     * @param string $personalIdentityNumber
     */
    public function testValid(string $personalIdentityNumber)
    {
        self::assertNotNull(new SwedenPersonalIdentificationNumber($personalIdentityNumber));
    }

    /**
     * @dataProvider invalidPersonalIdentityNumbers
     * @param string $personalIdentityNumber
     */
    public function testInvalid(string $personalIdentityNumber)
    {
        self::expectException(InvalidArgumentException::class);

        new SwedenPersonalIdentificationNumber($personalIdentityNumber);
    }

    /**
     * @dataProvider validPersonalIdentityNumbers
     * @param string $personalIdentityNumber
     */
    public function testToString(string $personalIdentityNumber)
    {
        self::assertSame(
            $personalIdentityNumber,
            (new SwedenPersonalIdentificationNumber($personalIdentityNumber))->__toString()
        );
    }

    /**
     * @dataProvider validPersonalIdentityNumbers
     * @param string $personalIdentityNumber
     */
    public function testCountryCode(string $personalIdentityNumber)
    {
        $snin = new SwedenPersonalIdentificationNumber($personalIdentityNumber);

        self::assertSame(SwedenPersonalIdentificationNumber::COUNTRY_CODE, $snin->getCountryCode());
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
            [''],
            ['abc'],
            ['1902282258'], // missing separator
            ['123456-7890'],
            ['190228-4048'], // valid date, incorrect checksum
            ['190229-4048'], // correct checksum, invalid date
        ];
    }
}
