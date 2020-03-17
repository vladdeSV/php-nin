<?php

declare(strict_types=1);

namespace NIN\NationalIdentificationNumbers;

interface NationalIdentificationNumberInterface
{
    public static function parse(string $nationalIdentificationNumber): NationalIdentificationNumberInterface;

    public function getCountryCode(): string;

    public function __toString();
}
