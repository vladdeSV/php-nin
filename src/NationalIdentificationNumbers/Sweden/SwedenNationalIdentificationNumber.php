<?php
declare(strict_types=1);

namespace NIN\NationalIdentificationNumbers\Sweden;

use DateTimeImmutable;
use NIN\NationalIdentificationNumbers\NationalIdentificationNumberInterface;

abstract class SwedenNationalIdentificationNumber implements NationalIdentificationNumberInterface
{
    public const COUNTRY_CODE = 'SE';

    public function __construct(DateTimeImmutable $dateTime, int $individualNumber)
    {
        $this->dateTime = $dateTime;
        $this->individualNumber = $individualNumber;
    }

    public static function calculateChecksumFromDateTimeAndIndividualNumber(DateTimeImmutable $dateTime, int $individualNumber, bool $isCoordinationNumber): int
    {
        $day = (int)$dateTime->format('d') + ($isCoordinationNumber ? 60 : 0);
        $nin = sprintf('%02d%02d%02d%03d', (int)$dateTime->format('y'), (int)$dateTime->format('m'), $day, $individualNumber);

        $numbers = array_map(function ($number): int {
            return (int)$number;
        }, str_split($nin));

        $modifiedNumbers = '';
        foreach ($numbers as $index => $number) {
            $multiplier = ($index % 2 === 0) ? 2 : 1;

            $modifiedNumbers .= $number * $multiplier;
        }

        $numbers = array_map(function ($number): int {
            return (int)$number;
        }, str_split($modifiedNumbers));

        return (10 - (array_sum($numbers) % 10)) % 10;
    }

    protected static function getSeparatorFromDateTime(DateTimeImmutable $dateTime): string
    {
        return ($dateTime->diff(new DateTimeImmutable())->y >= 100) ? '+' : '-';
    }

    final public function getCountryCode(): string
    {
        return self::COUNTRY_CODE;
    }

    abstract public function __toString(): string;

    /**
     * @var DateTimeImmutable
     */
    protected $dateTime;

    /**
     * @var int
     */
    protected $individualNumber;
}