<?php

declare(strict_types=1);

namespace NationalIdentificationNumber;

interface NationalIdentificationNumberInterface
{
    public static function parse(string $nationalIdentificationNumber): ?self;

    public function __toString();
}
