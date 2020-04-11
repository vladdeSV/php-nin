<?php

declare(strict_types=1);

namespace NIN\NationalIdentificationNumbers;

use DateTimeImmutable;
use InvalidArgumentException;

/**
 * - Follows format "DDMMYYNNNCC"
 *   - DD = day, two digit
 *   - MM = month, two digit
 *   - YY = year, two digit
 *   - NNN = individual number / century group, three digit
 *   - CC = checksums, two digit
 * - Individual numbers are even for females and odd for males
 * - Checksum studied from
 *   - github: svenheden/norwegian-birth-number-validator
 */
class NorwayBirthNumber implements NationalIdentificationNumberInterface
{
    public const COUNTRY_CODE = 'NO';

    private const REGEX_BIRTH_NUMBER = /** @lang PhpRegExp */
        '/^(?<DD>\d{2})(?<MM>\d{2})(?<YY>\d{2})(?<individualNumber>\d{3})(?<checksum>\d{2})$/';

    public function __construct(string $nationalIdentificationNumber)
    {
        $matches = [];
        if (!preg_match(self::REGEX_BIRTH_NUMBER, $nationalIdentificationNumber, $matches)) {
            throw new InvalidArgumentException('Invalid format.');
        }

        $YY = (int)$matches['YY'];
        $MM = (int)$matches['MM'];
        $DD = (int)$matches['DD'];
        $individualNumber = (int)$matches['individualNumber'];
        $checksum = (int)$matches['checksum'];

        $isDNumber = $DD >= 41 && $DD <= 71;
        if ($isDNumber) {
            $DD -= 40;
        }

        $isHNumber = $MM >= 41 && $MM <= 52;
        if ($isHNumber) {
            $MM -= 40;
        }

        if ($isDNumber && $isHNumber) {
            throw new InvalidArgumentException("Cannot be both D-number and H-number.");
        }

        $year = $this->getYearFromIndividualNumberAndTwoDigitYear($individualNumber, $YY);

        if (!checkdate((int)$MM, (int)$DD, (int)$year)) {
            throw new InvalidArgumentException("Invalid date.");
        }

        $date = DateTimeImmutable::createFromFormat('Y-m-d', "$year-$MM-$DD");

        if ($this->calculateChecksumFromDateAndIndividualNumber($date, $individualNumber, $isDNumber, $isHNumber) !== $checksum) {
            throw new InvalidArgumentException("Invalid checksum $nationalIdentificationNumber.");
        }

        $this->dateTime = $date;
        $this->individualNumber = $individualNumber;
        $this->isDNumber = $isDNumber;
        $this->isHNumber = $isHNumber;
    }

    public function __toString(): string
    {
        $day = (int)$this->dateTime->format('d');
        if ($this->isDNumber) {
            $day += 40;
        }

        $month = (int)$this->dateTime->format('m');
        if ($this->isHNumber) {
            $month += 40;
        }

        $checksum = $this->calculateChecksumFromDateAndIndividualNumber($this->dateTime, $this->individualNumber, $this->isDNumber, $this->isHNumber);

        return sprintf('%02d%02d%02d%03d%d', $day, $month, ((int)$this->dateTime->format('y')), $this->individualNumber, $checksum);
    }

    public function getCountryCode(): string
    {
        return self::COUNTRY_CODE;
    }

    private function calculateChecksumFromDateAndIndividualNumber(DateTimeImmutable $date, int $individualNumber, bool $isDNumber, bool $isHNumber): int
    {
        $day = (int)$date->format('d') + ($isDNumber ? 40 : 0);
        $month = (int)$date->format('m') + ($isHNumber ? 40 : 0);
        $numbers = sprintf('%02d%02d%02d%03d', $day, $month, (int)$date->format('y'), $individualNumber);

        $k1 = 11 - ((3 * $numbers[0] + 7 * $numbers[1] + 6 * $numbers[2] + 1 * $numbers[3] + 8 * $numbers[4] + 9 * $numbers[5] + 4 * $numbers[6] + 5 * $numbers[7] + 2 * $numbers[8]) % 11);
        $k2 = 11 - ((5 * $numbers[0] + 4 * $numbers[1] + 3 * $numbers[2] + 2 * $numbers[3] + 7 * $numbers[4] + 6 * $numbers[5] + 5 * $numbers[6] + 4 * $numbers[7] + 3 * $numbers[8] + 2 * $k1) % 11);

        $str = $k1 . $k2;
        return (int)$str;
    }

    private function getYearFromIndividualNumberAndTwoDigitYear(int $individualNumber, int $twoDigitYear)
    {
        /*
         * 500–749 indiviual number means between 1854–1899 (54-99)
         * 900–999 indiviual number means between 1940–1999 (40-99)
         * 000-499 indiviual number means between 1900–1999 (00-99)
         * 500–999 indiviual number means between 2000–2039 (00-39)
         */

        $year = null;

        if ($this->isNumberInRange($individualNumber, 500, 749) && $this->isNumberInRange($twoDigitYear, 54, 99)) {
            $year = (int)("18{$twoDigitYear}");
        } else if ($this->isNumberInRange($individualNumber, 499, 999) && $this->isNumberInRange($twoDigitYear, 40, 99)) {
            // special case for people born between 1940 -> 1999, span also includes 900-999
            $year = (int)("19{$twoDigitYear}");
        } else if ($this->isNumberInRange($individualNumber, 0, 499) && $this->isNumberInRange($twoDigitYear, 00, 99)) {
            $year = (int)("19{$twoDigitYear}");
        } else if ($this->isNumberInRange($individualNumber, 500, 999) && $this->isNumberInRange($twoDigitYear, 00, 39)) {
            $year = (int)("20{$twoDigitYear}");
        }

        return $year;
    }

    private function isNumberInRange(int $number, int $a, int $b): bool
    {
        return $number >= $a && $number <= $b;
    }

    /**
     * @var DateTimeImmutable
     */
    private $dateTime;

    /**
     * @var int Three digit integer
     */
    private $individualNumber;

    /**
     * D-numbers are assigned to people who temporarily reside in the country, eg. sailors and the tourist industry
     *
     * @var bool
     */
    private $isDNumber;

    /**
     * @var bool
     */
    private $isHNumber;
}
