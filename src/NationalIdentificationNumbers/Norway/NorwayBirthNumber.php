<?php
declare(strict_types=1);

namespace NIN\NationalIdentificationNumbers\Norway;

class NorwayBirthNumber extends NorwayNationalIdentificationNumber
{
    public function __toString(): string
    {
        $checksum = self::calculateChecksumFromDateAndIndividualNumber($this->dateTime, $this->individualNumber, false, false);

        return sprintf('%02d%02d%02d%03d%d', (int)$this->dateTime->format('d'), (int)$this->dateTime->format('m'), (int)$this->dateTime->format('y'), $this->individualNumber, $checksum);
    }
}