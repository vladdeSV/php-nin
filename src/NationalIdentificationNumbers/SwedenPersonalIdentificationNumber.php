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
        '/^(?<YY>\d{2})(?<MM>\d{2})(?<DD>\d{2})(?<separator>[-+])(?<individualNumber>\d{3})(?<checksum>\d)$/';

    private const REGEX_PERSONAL_IDENTITY_NUMBER_FULL = /** @lang PhpRegExp */
        '/^(?<YYYY>\d{4})(?<MM>\d{2})(?<DD>\d{2})(?<individualNumber>\d{3})(?<checksum>\d)$/';

    public function __construct(string $personalIdentificationNumber)
    {
        ['date' => $date, 'individualNumber' => $individualNumber, 'checksum' => $checksum, 'isCoordinationNumber' => $isCoordinationNumber] = $this->getInformationFromPersonalIdentificationNumber($personalIdentificationNumber);

        if ($date === null) {
            throw new InvalidArgumentException("Invalid date.");
        }

        if (!$this->isValidPersonalIdentityNumber($date, $individualNumber, $checksum)) {
            throw new InvalidArgumentException("Invalid personal identity number.");
        }

        $this->dateTime = $date;
        $this->individualNumber = $individualNumber;
        $this->checksum = $checksum;
        $this->isCoordinationNumber = $isCoordinationNumber;
    }

    public function getCountryCode(): string
    {
        return self::COUNTRY_CODE;
    }

    public function __toString(): string
    {
        $separator = $this->dateTime->diff(new DateTimeImmutable())->y >= 100 ? '+' : '-';

        $day = (int)$this->dateTime->format('d');
        if ($this->isCoordinationNumber) {
            $day += 60;
        }

        return sprintf('%02d%02d%02d%s%03d%d', ((int)$this->dateTime->format('Y')) % 100, (int)$this->dateTime->format('m'), $day, $separator, $this->individualNumber, $this->checksum);
    }

    private function calculateYearFromTwoDigitYearAndSeparator(int $twoDigitYear, string $separator): int
    {
        $currentYear = (int)(new DateTimeImmutable())->format('Y');
        $year = ((int)(($currentYear - $twoDigitYear) / 100)) * 100 + $twoDigitYear;
        if ($separator === '+') {
            $year -= 100;
        }

        return $year;
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

        $year = null;
        if (preg_match(self::REGEX_PERSONAL_IDENTITY_NUMBER, $personalIdentificationNumber, $matches)) {
            $twoDigitYear = (int)$matches['YY'];
            $separator = $matches['separator'];
            $year = $this->calculateYearFromTwoDigitYearAndSeparator($twoDigitYear, $separator);

        } else if (preg_match(self::REGEX_PERSONAL_IDENTITY_NUMBER_FULL, $personalIdentificationNumber, $matches)) {
            $year = (int)$matches['YYYY'];
        } else {
            throw new InvalidArgumentException('Invalid format.');
        }

        $month = (int)$matches['MM'];
        $day = (int)$matches['DD'];

        $individualNumber = (int)$matches['individualNumber'];
        $checksum = (int)$matches['checksum'];

        $isCoordinationNumber = $this->isCoordinationNumber($day);
        if ($isCoordinationNumber) {
            $day -= 60;
        }

        $date = null;
        if (checkdate($month, $day, $year)) {
            $date = DateTimeImmutable::createFromFormat('Y-m-d', "$year-$month-$day");
        }

        return ['date' => $date, 'individualNumber' => $individualNumber, 'checksum' => $checksum, 'isCoordinationNumber' => $isCoordinationNumber];
    }

    private function isCoordinationNumber(int $day): bool
    {
        return $day > 60 && $day <= (31 + 60);
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

    /**
     * @var bool
     */
    private $isCoordinationNumber;
}
