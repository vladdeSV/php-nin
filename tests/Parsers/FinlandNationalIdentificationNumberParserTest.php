<?php

declare(strict_types=1);

namespace NIN\Tests\Parsers;

use InvalidArgumentException;
use NIN\NationalIdentificationNumbers\Finland\FinlandPersonalIdentificationCode;
use NIN\Parsers\FinlandNationalIdentificationNumberParser;
use PHPUnit\Framework\TestCase;

class FinlandNationalIdentificationNumberParserTest extends TestCase
{
    /**
     * @dataProvider validPersonalIdentityCodes
     * @param string $personalIdentityCode
     */
    public function testValidPersonalIdentityCode(string $personalIdentityCode)
    {
        self::assertNotNull(FinlandNationalIdentificationNumberParser::parse($personalIdentityCode));
    }

    /**
     * @dataProvider invalidPersonalIdentityCodes
     * @param string $personalIdentityCode
     */
    public function testInvalidPersonalIdentityCode($personalIdentityCode)
    {
        self::expectException(InvalidArgumentException::class);

        FinlandNationalIdentificationNumberParser::parse($personalIdentityCode);
    }

    /**
     * @dataProvider validPersonalIdentityCodes
     * @param string $personalIdentityCode
     */
    public function testToString(string $personalIdentityCode)
    {
        $fnin = FinlandNationalIdentificationNumberParser::parse($personalIdentityCode);
        self::assertNotNull($fnin);
        self::assertSame($personalIdentityCode, $fnin->__toString());
    }

    /**
     * @dataProvider validPersonalIdentityCodes
     * @param string $personalIdentityCode
     */
    public function testGetCountryCode(string $personalIdentityCode)
    {
        $fnin = FinlandNationalIdentificationNumberParser::parse($personalIdentityCode);
        self::assertSame(FinlandPersonalIdentificationCode::COUNTRY_CODE, $fnin->getCountryCode());
    }

    public function validPersonalIdentityCodes(): array
    {
        return [
            ['131052-308T'],
            ['131052+308T'],
            ['131052A308T'],
            ['131052-9085'], // temporary number
        ];
    }

    public function invalidPersonalIdentityCodes(): array
    {
        return [
            ['131052308T'],
            ['abc123'],
            [''],
            ['290219A3085'],
            ['131052-001T'],
            ['131052-308A'], // invalid checksum
            ['131052.308T'],
        ];
    }
}
