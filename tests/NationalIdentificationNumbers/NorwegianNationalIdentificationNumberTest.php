<?php

declare(strict_types=1);

namespace NIN\Tests\NationalIdentificationNumbers;

use InvalidArgumentException;
use NIN\NationalIdentificationNumbers\NorwegianNationalIdentificationNumber;
use PHPUnit\Framework\TestCase;

class NorwegianNationalIdentificationNumberTest extends TestCase
{

    public function testParseValid()
    {
        self::assertNotNull(NorwegianNationalIdentificationNumber::parse('01129955131'));
    }

    public function testParseInvalid()
    {
        $this->expectException(InvalidArgumentException::class);

        NorwegianNationalIdentificationNumber::parse('01129955132');
    }

    public function testToString()
    {
        $nnin = NorwegianNationalIdentificationNumber::parse('01129955131');
        self::assertSame('01129955131', $nnin->__toString());
    }

    public function testGetCountryCode()
    {
        $nnin = NorwegianNationalIdentificationNumber::parse('01129955131');
        assertSame('no', $nnin->getCountryCode());
    }
}
