<?php

declare(strict_types=1);

namespace NationalIdentificationNumber\Tests\Helpers;

use Exception;
use InvalidArgumentException;
use NationalIdentificationNumber\Helpers\NationalIdentificationNumberParser;
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
        self::assertNotNull(NationalIdentificationNumberParser::parse($nin, $countryCode));
    }

    /**
     * @dataProvider nationalIdentityNumberCountry
     * @param string $nin
     * @param string $countryCode
     */
    public function testDetectCountry(string $nin, string $countryCode)
    {
        self::assertSame($countryCode, NationalIdentificationNumberParser::detectCountry($nin));
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

    public function testDetectInvalidCountry()
    {
        self::assertNull(NationalIdentificationNumberParser::detectCountry('abc123'));
    }

    public function nationalIdentityNumberCountry(): array
    {
        return [
            ['790315-0667', 'se'],
        ];
    }
}