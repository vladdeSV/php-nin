<?php
declare(strict_types=1);

namespace NIN\NationalIdentificationNumbers\Norway;

class NorwayHNumber extends NorwayNationalIdentificationNumber
{
    public function __toString(): string
    {
        $month = (int)$this->dateTime->format('m') + 40;
        $checksum = self::calculateChecksumFromDateAndIndividualNumber($this->dateTime, $this->individualNumber, false, true);

        return sprintf('%02d%02d%02d%03d%d', (int)$this->dateTime->format('d'), $month, (int)$this->dateTime->format('y'), $this->individualNumber, $checksum);
    }
}