<?php

declare(strict_types=1);

namespace NIN\NationalIdentificationNumbers;

use DateTimeImmutable;
use InvalidArgumentException;

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
class SwedenPersonalIdentificationNumber implements NationalIdentificationNumberInterface
{
    public const COUNTRY_CODE = 'SE';

    private const REGEX_PERSONAL_IDENTITY_NUMBER = /** @lang PhpRegExp */
        '/^(\d{6})([-+])(\d{3})(\d)$/';

    private const REGEX_PERSONAL_IDENTITY_NUMBER_FULL = /** @lang PhpRegExp */
        '/^((\d{4})(\d{2})(\d{2}))(\d{3})(\d)$/';

    public function __construct(string $personalIdentificationNumber)
    {
        ['date' => $date, 'individualNumber' => $individualNumber, 'checksum' => $checksum] = $this->getInformationFromPersonalIdentificationNumber($personalIdentificationNumber);

        if ($date === null) {
            throw new InvalidArgumentException("Invalid date.");
        }

        if (!$this->isValidPersonalIdentityNumber($date, $individualNumber, $checksum)) {
            throw new InvalidArgumentException("Invalid personal identity number.");
        }

        $this->dateTime = $date;
        $this->individualNumber = $individualNumber;
        $this->checksum = $checksum;
    }

    public function getCountryCode(): string
    {
        return self::COUNTRY_CODE;
    }

    public function __toString(): string
    {
        $separator = $this->dateTime->diff(new DateTimeImmutable())->y >= 100 ? '+' : '-';
        return sprintf('%02d%02d%02d%s%03d%d', ((int)$this->dateTime->format('Y')) % 100, (int)$this->dateTime->format('m'), (int)$this->dateTime->format('d'), $separator, $this->individualNumber, $this->checksum);
    }

    private function calculateDate(int $twoDigitYear, int $month, int $day, string $separator): ?DateTimeImmutable
    {
        $currentYear = (int)(new DateTimeImmutable())->format('Y');
        $year = ((int)(($currentYear - $twoDigitYear) / 100)) * 100 + $twoDigitYear;
        if ($separator === '+') {
            $year -= 100;
        }

        if (!checkdate($month, $day, $year)) {
            return null;
        }

        return DateTimeImmutable::createFromFormat('Y-m-d', "$year-$month-$day");
    }

    private function isValidPersonalIdentityNumber(DateTimeImmutable $dateTime, int $individualNumber, int $checksum): bool
    {
        $nin = sprintf('%02d%02d%02d%03d', ((int)$dateTime->format('Y')) % 100, (int)$dateTime->format('m'), (int)$dateTime->format('d'), $individualNumber);

        $numbers = self::applyLuhnAlgorithm(array_map(function ($number) {
            return (int)$number;
        }, str_split($nin)));
        return $checksum === self::calculateValueChecksum(array_sum($numbers));
    }

    /**
     * @param int[] $numbers
     * @return int[]
     */
    private function applyLuhnAlgorithm(array $numbers): array
    {
        $modifiedNumbers = '';
        foreach ($numbers as $index => $number) {
            $multiplier = ($index % 2 === 0) ? 2 : 1;

            $modifiedNumbers .= ($number * $multiplier);
        }

        return array_map(function ($number) {
            return (int)$number;
        }, str_split($modifiedNumbers));
    }

    private function calculateValueChecksum(int $number): int
    {
        // source: https://sv.wikipedia.org/wiki/Personnummer_i_Sverige#Kontrollsiffran
        return (10 - ($number % 10)) % 10;
    }

    private function getInformationFromPersonalIdentificationNumber(string $personalIdentificationNumber): array
    {
        $matches = [];
        if (preg_match(self::REGEX_PERSONAL_IDENTITY_NUMBER, $personalIdentificationNumber, $matches)) {

            $separator = $matches[2];
            $individualNumber = (int)$matches[3];
            $checksum = (int)$matches[4];

            [$twoDigitYear, $month, $day] = str_split($matches[1], 2);
            $twoDigitYear = (int)$twoDigitYear;
            $month = (int)$month;
            $day = (int)$day;

            $date = $this->calculateDate($twoDigitYear, $month, $day, $separator);

            return ['date' => $date, 'individualNumber' => $individualNumber, 'checksum' => $checksum];

        } else if (preg_match(self::REGEX_PERSONAL_IDENTITY_NUMBER_FULL, $personalIdentificationNumber, $matches)) {

            $year = (int)$matches[2];
            $month = (int)$matches[3];
            $day = (int)$matches[4];
            $individualNumber = (int)$matches[5];
            $checksum = (int)$matches[6];

            $date = null;
            if (checkdate($month, $day, $year)) {
                $date = DateTimeImmutable::createFromFormat('Y-m-d', "$year-$month-$day");
            }

            return ['date' => $date, 'individualNumber' => $individualNumber, 'checksum' => $checksum];
        } else {
            throw new InvalidArgumentException('Invalid format.');
        }
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
     * @var int
     */
    private $checksum;
}
