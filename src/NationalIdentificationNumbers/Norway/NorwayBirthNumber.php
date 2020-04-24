<?php

namespace NIN\NationalIdentificationNumbers\Norway;

use NIN\Parsers\NorwayNationalIdentificationNumberParser;

class NorwayBirthNumber extends NorwayNationalIdentificationNumber
{
    public function __toString(): string
    {
        $checksum = NorwayNationalIdentificationNumberParser::calculateChecksumFromDateAndIndividualNumber($this->dateTime, $this->individualNumber, false, false);

        return sprintf('%02d%02d%02d%03d%d', (int)$this->dateTime->format('d'), (int)$this->dateTime->format('m'), ((int)$this->dateTime->format('y')), $this->individualNumber, $checksum);
    }
}