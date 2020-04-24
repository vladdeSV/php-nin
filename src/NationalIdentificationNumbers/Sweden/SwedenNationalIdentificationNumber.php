<?php

namespace NIN\NationalIdentificationNumbers\Sweden;

use DateTimeImmutable;
use NIN\NationalIdentificationNumbers\NationalIdentificationNumberInterface;

abstract class SwedenNationalIdentificationNumber implements NationalIdentificationNumberInterface
{
    public const COUNTRY_CODE = 'SE';

    protected static function getSeparatorFromDateTime(DateTimeImmutable $dateTime): string
    {
        return $dateTime->diff(new DateTimeImmutable())->y >= 100 ? '+' : '-';
    }

    public final function getCountryCode(): string
    {
        return self::COUNTRY_CODE;
    }

    public abstract function __toString(): string;
}