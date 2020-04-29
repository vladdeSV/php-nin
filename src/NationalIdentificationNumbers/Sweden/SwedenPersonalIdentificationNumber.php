<?php

declare(strict_types=1);

namespace NIN\NationalIdentificationNumbers\Sweden;

class SwedenPersonalIdentificationNumber extends SwedenNationalIdentificationNumber
{
    public function __toString(): string
    {
        $separator = self::getSeparatorFromDateTime($this->dateTime);
        $checksum = self::calculateChecksumFromDateTimeAndIndividualNumber($this->dateTime, $this->individualNumber, false);

        return sprintf('%02d%02d%02d%s%03d%d', (int)$this->dateTime->format('Y') % 100, (int)$this->dateTime->format('m'), (int)$this->dateTime->format('d'), $separator, $this->individualNumber, $checksum);
    }
}
