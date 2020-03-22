<?php

declare(strict_types=1);

namespace NIN\Tests\NationalIdentificationNumbers;

use InvalidArgumentException;
use NIN\NationalIdentificationNumbers\FinlandPersonalIdentificationCode;
use PHPUnit\Framework\TestCase;

class FinlandPersonalIdentificationCodeTest extends TestCase
{
    /**
     * @dataProvider validPersonalIdentityCodes
     * @param string $personalIdentityCode
     */
    public function testValidPersonalIdentityCode(string $personalIdentityCode)
    {
        self::assertNotNull(new FinlandPersonalIdentificationCode($personalIdentityCode));
    }

    /**
     * @dataProvider invalidPersonalIdentityCodes
     * @param string $personalIdentityCode
     */
    public function testInvalidPersonalIdentityCode($personalIdentityCode)
    {
        self::expectException(InvalidArgumentException::class);

        new FinlandPersonalIdentificationCode($personalIdentityCode);
    }

    /**
     * @dataProvider validPersonalIdentityCodes
     * @param string $personalIdentityCode
     */
    public function testToString(string $personalIdentityCode)
    {
        $fnin = new FinlandPersonalIdentificationCode($personalIdentityCode);
        self::assertNotNull($fnin);
        self::assertSame($personalIdentityCode, $fnin->__toString());
    }

    public function validPersonalIdentityCodes(): array
    {
        return [
            ['131052-308T'],
        ];
    }

    public function invalidPersonalIdentityCodes(): array
    {
        return [
            ['131052308T'],
            ['abc123'],
            [''],
            ['290219A3085'],
        ];
    }
}
