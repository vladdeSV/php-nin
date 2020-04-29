<?php

declare(strict_types=1);

namespace NIN\NationalIdentificationNumbers\Norway;

class NorwayDNumber extends NorwayNationalIdentificationNumber
{
    public function __toString(): string
    {
        $day = (int)$this->dateTime->format('d') + 40;
        $checksum = self::calculateChecksumFromDateAndIndividualNumber($this->dateTime, $this->individualNumber, true, false);

        return sprintf('%02d%02d%02d%03d%d', $day, (int)$this->dateTime->format('m'), (int)$this->dateTime->format('y'), $this->individualNumber, $checksum);
    }
}
