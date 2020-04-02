<?php

declare(strict_types=1);

namespace NIN\NationalIdentificationNumbers;

interface NationalIdentificationNumberInterface
{
    public function getCountryCode(): string;

    public function __toString(): string;
}
