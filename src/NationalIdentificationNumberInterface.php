<?php

declare(strict_types=1);

namespace NationalIdentificationNumber;

interface NationalIdentificationNumberInterface
{
    public static function parse(string $nationalIdentificationNumber): ?NationalIdentificationNumberInterface;

    public function __toString();
}
