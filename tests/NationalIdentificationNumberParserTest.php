<?php

declare(strict_types=1);

namespace NIN\Tests;

use Exception;
use InvalidArgumentException;
use NIN\NationalIdentificationNumberParser;
use PHPUnit\Framework\TestCase;

class NationalIdentificationNumberParserTest extends TestCase
{
    /**
     * @dataProvider nationalIdentityNumberCountry
     * @param string $nin
     * @param string $countryCode
     * @throws Exception
     */
    public function testParseValid(string $nin, string $countryCode)
    {
        $nationalIdentificationNumber = NationalIdentificationNumberParser::parse($nin, $countryCode);
        self::assertNotNull($nationalIdentificationNumber);
        self::assertSame($nin, $nationalIdentificationNumber->__toString());
        self::assertSame($countryCode, $nationalIdentificationNumber->getCountryCode());
    }

    /** @noinspection PhpUnhandledExceptionInspection */
    public function testParseInvalidNationalIdentificationNumber()
    {
        self::expectException(InvalidArgumentException::class);

        NationalIdentificationNumberParser::parse('abc123', 'SE');
    }

    public function testInvalidCountry()
    {
        self::expectException(Exception::class);

        NationalIdentificationNumberParser::parse('', 'xx');
    }

    public function testUnsupportedCountry()
    {
        self::expectException(Exception::class);

        NationalIdentificationNumberParser::parse('', 'PL');
    }

    public function testTryParse()
    {
        self::assertNull(NationalIdentificationNumberParser::tryParse('', 'SE'));
        self::assertNull(NationalIdentificationNumberParser::tryParse('foobar', ''));

        self::assertNotNull(NationalIdentificationNumberParser::tryParse('990214+0095', 'SE'));
    }

    public function nationalIdentityNumberCountry(): array
    {
        return [
            ['790315-0667', 'SE'],
            ['15121015649', 'NO'],
            ['131052-308T', 'FI'],
            ['1201603389', 'IS'],
            ['211062-5629', 'DK'],
        ];
    }
}
