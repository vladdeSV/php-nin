<?php

declare(strict_types=1);

namespace NationalIdentificationNumber\NationalIdentificationNumbers;

use DateTimeImmutable;
use InvalidArgumentException;

class SwedenNationalIdentificationNumber implements NationalIdentificationNumberInterface
{
    public static function parse(string $nationalIdentificationNumber): NationalIdentificationNumberInterface
    {
        $matches = [];
        if (!preg_match(self::REGEX_PERSONAL_IDENTITY_NUMBER, $nationalIdentificationNumber, $matches)) {
            throw new InvalidArgumentException('Invalid format. Must follow YYMMDD±XXXX');
        }

        $separator = $matches[2];
        $individualNumber = (int)$matches[3];
        $checksum = (int)$matches[4];

        [$year, $month, $day] = str_split($matches[1], 2);
        $year = (int)$year;
        $month = (int)$month;
        $day = (int)$day;

        $date = self::calculateDate($year, $month, $day, $separator);
        if ($date === null) {
            throw new InvalidArgumentException("Invalid date. {$year}-{$month}-{$day} does not exist.");
        }

        if (!self::isValidPersonalIdentityNumber($date, $individualNumber, $checksum)) {
            throw new InvalidArgumentException("Invalid personal identity number.");
        }

        return new self($date, $individualNumber, $checksum);
    }

    public function getCountryCode(): string
    {
        return 'se';
    }

    public function __toString()
    {
        $separator = $this->dateTime->diff(new DateTimeImmutable())->y >= 100 ? '+' : '-';
        return sprintf('%02d%02d%02d%s%d%d', ((int)$this->dateTime->format('Y')) % 100, (int)$this->dateTime->format('m'), (int)$this->dateTime->format('d'), $separator, $this->individualNumber, $this->checksum);
    }

    private static function calculateDate(int $twoDigitYear, int $month, int $day, string $separator): ?DateTimeImmutable
    {
        $currentYear = (int)(new DateTimeImmutable())->format('Y');
        $year = ((int)(($currentYear - $twoDigitYear) / 100)) * 100 + $twoDigitYear;
        if ($separator === '+') {
            $year -= 100;
        }

        if (!checkdate($month, $day, $year)) {
            return null;
        }

        $str = "$year-$month-$day";
        return DateTimeImmutable::createFromFormat('Y-m-d', $str);
    }

    private static function isValidPersonalIdentityNumber(DateTimeImmutable $dateTime, int $individualNumber, int $checksum): bool
    {
        $nin = sprintf('%02d%02d%02d%d', ((int)$dateTime->format('Y')) % 100, (int)$dateTime->format('m'), (int)$dateTime->format('d'), $individualNumber);

        $numbers = self::applyLuhnAlgorithm(array_map(function ($number) {
            return (int)$number;
        }, str_split($nin)));
        return $checksum === self::calculateValueChecksum(array_sum($numbers));
    }

    /**
     * @param int[] $numbers
     * @return int[]
     */
    private static function applyLuhnAlgorithm(array $numbers): array
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

    private static function calculateValueChecksum(int $number): int
    {
        // source: https://sv.wikipedia.org/wiki/Personnummer_i_Sverige#Kontrollsiffran
        return (10 - ($number % 10)) % 10;
    }

    private function __construct(DateTimeImmutable $dateTime, int $individualNumber, int $checksum)
    {
        $this->dateTime = $dateTime;
        $this->individualNumber = $individualNumber;
        $this->checksum = $checksum;
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

    private const REGEX_PERSONAL_IDENTITY_NUMBER = /** @lang PhpRegExp */
        '/^(\d{6})([-+])(\d{3})(\d)$/';
}