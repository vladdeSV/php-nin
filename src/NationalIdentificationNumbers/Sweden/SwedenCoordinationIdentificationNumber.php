<?php

declare(strict_types=1);

namespace NIN\NationalIdentificationNumbers\Sweden;

/**
 * - Follows following formats
 *   - "YYMMDD±NNNC"
 *     - YY = year, two digit
 *     - MM = month, two digit
 *     - DD = day, two digit
 *     - ± = "-" if less than 100 years old, otherwise "+"
 *     - NNN = individual number, three digit
 *     - C = checksum, single digit
 *   - "YYYYMMDDNNNC", however is not official standard
 *     - YYYY = year, four digit
 *     - MM = month, two digit
 *     - DD = day, two digit
 *     - NNN = individual number, three digit
 *     - C = checksum, single digit
 * - Individual numbers are even for females and odd for males
 * - Checksum studied from
 *   - https://sv.wikipedia.org/wiki/Personnummer_i_Sverige#Kontrollsiffran
 */
class SwedenCoordinationIdentificationNumber extends SwedenNationalIdentificationNumber
{
    public function __toString(): string
    {
        $separator = self::getSeparatorFromDateTime($this->dateTime);
        $checksum = self::calculateChecksumFromDateTimeAndIndividualNumber($this->dateTime, $this->individualNumber, true);

        return sprintf('%02d%02d%02d%s%03d%d', (int)$this->dateTime->format('Y') % 100, (int)$this->dateTime->format('m'), (int)$this->dateTime->format('d') + 60, $separator, $this->individualNumber, $checksum);
    }
}
