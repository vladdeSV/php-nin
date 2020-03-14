<?php

declare(strict_types=1);

namespace NationalIdentificationNumber\Tests;

use PHPUnit\Framework\TestCase;
use NationalIdentificationNumber\SwedenNationalIdentificationNumber;

class SwedenNationalIdentificationNumberTest extends TestCase
{
    public function testValid()
    {
        self::assertNotNull(SwedenNationalIdentificationNumber::parse('190228-2258'));
        self::assertNotNull(SwedenNationalIdentificationNumber::parse('730512-4609'));
        self::assertNotNull(SwedenNationalIdentificationNumber::parse('180910+4068'));
        self::assertNotNull(SwedenNationalIdentificationNumber::parse('690628-3384'));
    }

    public function testInvalid()
    {
        self::assertNull(SwedenNationalIdentificationNumber::parse('abc'));
        self::assertNull(SwedenNationalIdentificationNumber::parse('1902282258')); // missing separator
        self::assertNull(SwedenNationalIdentificationNumber::parse('123456-7890'));

        self::assertNull(SwedenNationalIdentificationNumber::parse('190228-4048')); // valid date, incorrect checksum
        self::assertNull(SwedenNationalIdentificationNumber::parse('190229-4048')); // correct checksum, invalid date
    }

    public function testToString()
    {
        $snin = SwedenNationalIdentificationNumber::parse('190228-2258');
        self::assertSame('190228-2258', $snin->__toString());

        $snin = SwedenNationalIdentificationNumber::parse('190228+2258');
        self::assertSame('190228+2258', $snin->__toString());

        $snin = SwedenNationalIdentificationNumber::parse('200314+4355');
        self::assertSame('200314+4355', $snin->__toString());

        $snin = SwedenNationalIdentificationNumber::parse('200314-4355');
        self::assertSame('200314-4355', $snin->__toString());
    }
}
