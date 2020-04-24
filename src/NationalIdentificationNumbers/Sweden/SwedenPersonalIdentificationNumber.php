<?php

declare(strict_types=1);

namespace NIN\NationalIdentificationNumbers\Sweden;

use DateTimeImmutable;
use NIN\Parsers\SwedenNationalIdentificationNumberParser;

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
class SwedenPersonalIdentificationNumber extends SwedenNationalIdentificationNumber
{
    public function __construct(DateTimeImmutable $dateTime, int $individualNumber)
    {
        $this->dateTime = $dateTime;
        $this->individualNumber = $individualNumber;
    }

    public function __toString(): string
    {
        $separator = self::getSeparatorFromDateTime($this->dateTime);
        $checksum = SwedenNationalIdentificationNumberParser::calculateChecksumFromDateTimeAndIndividualNumber($this->dateTime, $this->individualNumber, false);

        return sprintf('%02d%02d%02d%s%03d%d', ((int)$this->dateTime->format('Y')) % 100, (int)$this->dateTime->format('m'), (int)$this->dateTime->format('d'), $separator, $this->individualNumber, $checksum);
    }

    /**
     * @var DateTimeImmutable
     */
    private $dateTime;

    /**
     * @var int
     */
    private $individualNumber;
}
