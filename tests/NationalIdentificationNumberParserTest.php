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

    public function testParseInvalidNationalIdentificationNumber()
    {
        self::expectException(InvalidArgumentException::class);

        NationalIdentificationNumberParser::parse('abc123', 'se');
    }

    public function testInvalidCountry()
    {
        self::expectException(Exception::class);

        NationalIdentificationNumberParser::parse('', 'xx');
    }

    public function testTryParse()
    {
        self::assertNull(NationalIdentificationNumberParser::tryParse('', 'se'));
        self::assertNull(NationalIdentificationNumberParser::tryParse('foobar', ''));

        self::assertNotNull(NationalIdentificationNumberParser::tryParse('990214+0095', 'se'));
    }

    public function nationalIdentityNumberCountry(): array
    {
        return [
            ['790315-0667', 'se'],
            ['15121015649', 'no'],
        ];
    }
}
