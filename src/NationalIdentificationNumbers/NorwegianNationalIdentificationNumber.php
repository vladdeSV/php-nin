<?php

declare(strict_types=1);

namespace NIN\NationalIdentificationNumbers;

use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Norwegian Birth Number
 *
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
class NorwegianNationalIdentificationNumber implements NationalIdentificationNumberInterface
{
    public static function parse(string $nationalIdentificationNumber): NationalIdentificationNumberInterface
    {
        $matches = [];
        if (!preg_match(self::REGEX_BIRTH_NUMBER, $nationalIdentificationNumber, $matches)) {
            throw new InvalidArgumentException('Invalid format. Must follow DDMMYYXXXXX');
        }

        $dateString = $matches[1];
        $individualNumber = $matches[2];
        $checksum = $matches[3];

        [$day, $month, $twoDigitYear] = str_split($dateString, 2);
        $isDNumber = false;

        if ($day >= 1 + 40 && $day <= 31 + 40) {
            $isDNumber = true;
            $day -= 40;
        }

        $year = self::getYearFromIndividualNumberAndTwoDigitYear($individualNumber, $twoDigitYear);

        if (!checkdate((int)$month, (int)$day, (int)$year)) {
            throw new InvalidArgumentException("Invalid date. {$year}-{$month}-{$day} does not exist.");
        }

        $date = DateTimeImmutable::createFromFormat('Y-m-d', "$year-$month-$day");

        $numbers = array_map(function (string $char) {
            return (int)$char;
        }, str_split($matches[0]));

        $validChecksum = self::validateChecksum($numbers);
        if (!$validChecksum) {
            throw new InvalidArgumentException("Invalid checksum.");
        }

        return new self($date, $isDNumber, $individualNumber, $checksum);
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        $day = (int)$this->dateTime->format('d');
        if ($this->isDNumber) {
            $day += 40;
        }

        return sprintf('%02d%02d%02d%03d%d', $day, (int)$this->dateTime->format('m'), ((int)$this->dateTime->format('y')), $this->individualNumber, $this->checksum);
    }

    public function getCountryCode(): string
    {
        return 'no';
    }

    private static function getYearFromIndividualNumberAndTwoDigitYear(string $individualNumber, string $twoDigitYear)
    {
        /*
         * 500–749 indiviual number means between 1854–1899 (54-99)
         * 900–999 indiviual number means between 1940–1999 (40-99)
         * 000-499 indiviual number means between 1900–1999 (00-99)
         * 500–999 indiviual number means between 2000–2039 (00-49)
         */

        $twoDigitYearNumber = (int)$twoDigitYear;
        $individualNumberNumber = (int)$individualNumber;

        if (self::isNumberInRange($individualNumberNumber, 500, 749) && self::isNumberInRange($twoDigitYearNumber, 54, 99)) {
            return (int)("18{$twoDigitYear}");
        }

        // special case for people born between 1940 -> 1999, span also includes 900-999
        if (self::isNumberInRange($individualNumberNumber, 499, 999) && self::isNumberInRange($twoDigitYearNumber, 40, 99)) {
            return (int)("19{$twoDigitYear}");
        }

        if (self::isNumberInRange($individualNumberNumber, 0, 499) && self::isNumberInRange($twoDigitYearNumber, 00, 99)) {
            return (int)("19{$twoDigitYear}");
        }

        if (self::isNumberInRange($individualNumberNumber, 500, 999) && self::isNumberInRange($twoDigitYearNumber, 00, 39)) {
            return (int)("20{$twoDigitYear}");
        }

        throw new InvalidArgumentException("Invalid birth number. Individual number '$individualNumber' is invalid for year '$twoDigitYear'.");

    }

    private static function isNumberInRange(int $number, int $a, int $b): bool
    {
        return $number >= $a && $number <= $b;
    }

    private static function validateChecksum(array $nnin)
    {
        return
            self::validateChecksumWithBirthNumberAndMultipliers($nnin, [3, 7, 6, 1, 8, 9, 4, 5, 2, 1]) &&
            self::validateChecksumWithBirthNumberAndMultipliers($nnin, [5, 4, 3, 2, 7, 6, 5, 4, 3, 2, 1]);
    }

    public static function validateChecksumWithBirthNumberAndMultipliers(array $birthNumber, array $multipliers): bool
    {
        $sum = array_sum(array_map(function ($multiplier, $index) use ($birthNumber) {
            return $multiplier * $birthNumber[$index];
        }, $multipliers, array_keys($multipliers)));

        return $sum % 11 === 0;
    }

    private function __construct(DateTimeImmutable $dateTime, bool $isDNumber, string $individualNumber, string $checksum)
    {
        $this->dateTime = $dateTime;
        $this->isDNumber = $isDNumber;
        $this->individualNumber = $individualNumber;
        $this->checksum = $checksum;
    }

    private const REGEX_BIRTH_NUMBER = /** @lang PhpRegExp */
        '/^(\d{6})(\d{3})(\d{2})$/';

    /**
     * @var DateTimeImmutable
     */
    private $dateTime;

    /**
     * @var string Three digit integer
     */
    private $individualNumber;

    /**
     * @var string Two digit integer
     */
    private $checksum;

    /**
     * D-numbers are assigned to people who temporarily reside in the country, eg. sailors and the tourist industry
     *
     * @var int
     */
    private $isDNumber;
}
