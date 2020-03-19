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
 *   - NNN = individual number, three digit
 *   - CC = checksum, two digit
 * - Individual numbers are even for females and odd for males
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
        $individualNumber = (int)$matches[2];
        $checksum = (int)$matches[3];

        [$day, $month, $twoDigitYear] = str_split($dateString, 2);

        $day = (int)$day;
        $month = (int)$month;
        $twoDigitYear = (int)$twoDigitYear;

        $isDNumber = false;

        //fixme for D-number, where 41 <= $month <= 52
        if ($month >= 41 && $month <= 52) {
            $isDNumber = true;
            $month -= 40;
        }

        $century = self::getCenturyFromIndividualNumber($individualNumber);
        $year = (int)($century . $twoDigitYear);
        if ($century === - 1) {
            throw new InvalidArgumentException("Invalid individual number. $individualNumber not match for year $fullYear.");
        }

        if (!checkdate($month, $day, $year)) {
            throw new InvalidArgumentException("Invalid date. {$year}-{$month}-{$day} does not exist.");
        }

        $date = DateTimeImmutable::createFromFormat('y-m-d', "$year-$month-$day");

        $validChecksum = self::validateChecksum(array_map(function (string $char) {
            return (int)$char;
        }, str_split($matches[1] . $matches[2])), $checksum);
        if (!$validChecksum) {
            throw new InvalidArgumentException("Invalid checksum.");
        }

        return new self($date, $isDNumber, $individualNumber, $checksum);
    }

    private function __construct(DateTimeImmutable $dateTime, bool $isDNumber, int $individualNumber, int $checksum)
    {
        $this->dateTime = $dateTime;
        $this->isDNumber = $isDNumber;
        $this->individualNumber = $individualNumber;
        $this->checksum = $checksum;
    }

    /**
     * 1854–1899 the range is 500–749
     * 1900–1999 the range is 000-499
     * (1940–1999 the range is also 900–999)
     * 2000–2039 the range is 500–999
     *
     * @param int $individualNumber
     * @param int $year
     * @return int
     */
    //fixme mgf
//    private static function getCenturyFromIndividualNumber(int $individualNumber)
//    {
//        if (self::isNumberInRange($year, 1854, 1899) && self::isNumberInRange($individualNumber, 500, 749)) {
//            return 18;
//        }
//
//        if (self::isNumberInRange($year, 1900, 1999) && self::isNumberInRange($individualNumber, 0, 499)) {
//            return 19;
//        }
//
//        // special case for people born between 1940 -> 1999, span also includes 900-999
//        if (self::isNumberInRange($year, 1940, 1999) && self::isNumberInRange($individualNumber, 499, 999)) {
//            return 19;
//        }
//
//        if (self::isNumberInRange($year, 2000, 2039) && self::isNumberInRange($individualNumber, 500, 999)) {
//            return 20;
//        }
//
//        return -1;
//    }

    private static function getCenturyFromIndividualNumber(int $individualNumber, int $twoDigitYear)
    {
        if (self::isNumberInRange($twoDigitYear, 54, 99) && self::isNumberInRange($individualNumber, 500, 749)) {
            return 18;
        }

        // special case for people born between 1940 -> 1999, span also includes 900-999
        if (self::isNumberInRange($twoDigitYear, 40, 99) && self::isNumberInRange($individualNumber, 499, 999)) {
            return 19;
        }

        if (self::isNumberInRange($twoDigitYear, 00, 99) && self::isNumberInRange($individualNumber, 0, 499)) {
            return 19;
        }

        if (self::isNumberInRange($twoDigitYear, 00, 39) && self::isNumberInRange($individualNumber, 500, 999)) {
            return 20;
        }

        return -1;
    }

    private static function isNumberInRange(int $number, int $a, int $b): bool
    {
        return $number >= $a && $number <= $b;
    }

    /**
     * @param int[] $numbers
     * @param int $checksum
     * @return bool
     */
    private static function validateChecksum(array $numbers, int $checksum): bool
    {
        $k1 = 11 - ((3 * $numbers[1] + 7 * $numbers[2] + 6 * $numbers[2 + 1] + 1 * $numbers[2 + 2] + 8 * $numbers[4 + 1] + 9 * $numbers[4 + 2] + 4 * $numbers[6 + 1] + 5 * $numbers[6 + 2] + 2 * $numbers[6 + 3]) % 11);
        $k2 = 11 - ((5 * $numbers[1] + 4 * $numbers[2] + 3 * $numbers[2 + 1] + 2 * $numbers[2 + 2] + 7 * $numbers[4 + 1] + 6 * $numbers[4 + 2] + 5 * $numbers[6 + 1] + 4 * $numbers[6 + 2] + 3 * $numbers[6 + 3] + 2 * $k1) % 11);

        return ((int)$k1 . $k2) === $checksum;
    }

    public function getCountryCode(): string
    {
        return 'no';
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        $month = (int)$this->dateTime->format('m');
        if ($this->isDNumber) {
            $month += 40;
        }

        return sprintf('%02d%02d%02d%d%d', ((int)$this->dateTime->format('Y')) % 100, $month, (int)$this->dateTime->format('d'), $this->individualNumber, $this->checksum);
    }

    /**
     * @var DateTimeImmutable
     */
    private $dateTime;

    /**
     * @var int
     */
    private $individualNumber;

    /**
     * @var int Two digit integer
     */
    private $checksum;

    /**
     * D-numbers are assigned to people who temporarily reside in the country, eg. sailors and the tourist industry
     *
     * @var int
     */
    private $isDNumber;

    private const REGEX_BIRTH_NUMBER = /** @lang PhpRegExp */
        '/^(\d{6})(\d{3})(\d){2}$/';
}
