<?php

declare(strict_types=1);

namespace NIN\Tests\NationalIdentificationNumbers;

use InvalidArgumentException;
use NIN\NationalIdentificationNumbers\NorwayBirthNumber;
use PHPUnit\Framework\TestCase;

/**
 * Example birth numbers taken from
 * - github: mikaello/norwegian-national-id-validator
 * - github: svenheden/norwegian-birth-number-validator
 */
class NorwayBirthNumberTest extends TestCase
{
    /**
     * @dataProvider validBirthNumbers
     *
     * @param $birthNumber
     */
    public function testParseValid(string $birthNumber)
    {
        self::assertNotNull(new NorwayBirthNumber($birthNumber));
    }

    /**
     * @dataProvider invalidBirthNumbers
     *
     * @param $birthNumber
     */
    public function testParseInvalid(string $birthNumber)
    {
        $this->expectException(InvalidArgumentException::class);

        new NorwayBirthNumber($birthNumber);
    }

    /**
     * @dataProvider validBirthNumbers
     *
     * @param $birthNumber
     */
    public function testToString(string $birthNumber)
    {
        $nnin = new NorwayBirthNumber($birthNumber);
        self::assertSame($birthNumber, $nnin->__toString());
    }

    /**
     * @dataProvider validBirthNumbers
     *
     * @param $birthNumber
     */
    public function testGetCountryCode(string $birthNumber)
    {
        $nnin = new NorwayBirthNumber($birthNumber);
        self::assertSame(NorwayBirthNumber::COUNTRY_CODE, $nnin->getCountryCode());
    }

    public function validBirthNumbers(): array
    {
        return [
            ['15121015649'],
            ['03098443559'],
            ['21081633352'],
            ['16074530617'],
            ['27075532585'],
            ['01010100131'],
            ['42059199212'],
            ['67047000642'],
        ];
    }

    public function invalidBirthNumbers(): array
    {
        return [
            [''],
            ['151210-15649'],
            ['12345'],
            ['123456789123456789'],
            ['abc'],
            ['191a0831-7574'],
            ['19610603!1757'],
            ['00000000000'],
            ['32121015683'],
            ['21131633340'],
            ['30025532542'],
            ['27075531585'],
            ['23011244588'],
            ['28158817947'],
            ['17014829936'],
            ['11089031893'],
        ];
    }
}
