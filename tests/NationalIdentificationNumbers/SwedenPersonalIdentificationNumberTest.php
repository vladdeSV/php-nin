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
     * @dataProvider validFullPersonalIdentityNumbers
     * @param string $personalIdentityNumber
     */
    public function testValidFullLength(string $personalIdentityNumber)
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
     * @dataProvider validFullPersonalIdentityNumbers
     * @param string $personalIdentityNumber12
     * @param string $personalIdentityNumber10
     */
    public function testFullLengthToString(string $personalIdentityNumber12, string $personalIdentityNumber10)
    {
        $snin = new SwedenPersonalIdentificationNumber($personalIdentityNumber12);

        self::assertSame(
            $personalIdentityNumber10,
            ($snin)->__toString()
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
            ['200374-4355'],
            ['180970+4068'],
            ['690688-3384']
        ];
    }

    /**
     * Each item is in format [12-digit, 10-digit]
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
