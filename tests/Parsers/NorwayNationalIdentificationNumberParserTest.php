<?php

declare(strict_types=1);

namespace NIN\Tests\Parsers;

use InvalidArgumentException;
use NIN\NationalIdentificationNumbers\Norway\NorwayNationalIdentificationNumber;
use NIN\Parsers\NorwayNationalIdentificationNumberParser;
use PHPUnit\Framework\TestCase;

/**
 * Example birth numbers taken from
 * - github: mikaello/norwegian-national-id-validator
 * - github: svenheden/norwegian-birth-number-validator
 */
class NorwayNationalIdentificationNumberParserTest extends TestCase
{
    /**
     * @dataProvider validBirthNumbers
     *
     * @param $birthNumber
     */
    public function testParseValid(string $birthNumber)
    {
        self::assertNotNull(NorwayNationalIdentificationNumberParser::parse($birthNumber));
    }

    /**
     * @dataProvider invalidBirthNumbers
     *
     * @param $birthNumber
     */
    public function testParseInvalid(string $birthNumber)
    {
        $this->expectException(InvalidArgumentException::class);

        NorwayNationalIdentificationNumberParser::parse($birthNumber);
    }

    /**
     * @dataProvider validBirthNumbers
     *
     * @param $birthNumber
     */
    public function testToString(string $birthNumber)
    {
        $nnin = NorwayNationalIdentificationNumberParser::parse($birthNumber);
        self::assertSame($birthNumber, $nnin->__toString());
    }

    /**
     * @dataProvider validBirthNumbers
     *
     * @param $birthNumber
     */
    public function testGetCountryCode(string $birthNumber)
    {
        $nnin = NorwayNationalIdentificationNumberParser::parse($birthNumber);
        self::assertSame(NorwayNationalIdentificationNumber::COUNTRY_CODE, $nnin->getCountryCode());
    }

    public function validBirthNumbers(): array
    {
        return [
            ['15121015649'],
            ['03098443559'],
            ['21081633352'],
            ['16074530617'],
            ['27075532585'],
            ['42059199212'],
            ['67047000642'],
            ['03498443531'],
            ['43098443542'],
        ];
    }

    public function invalidBirthNumbers(): array
    {
        return [
            [''],
            ['151210-15649'], // has separator
            ['43498443525'], // is both d-number and h-number
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
            ['42052099212'],
            ['42059069212'],
        ];
    }
}
