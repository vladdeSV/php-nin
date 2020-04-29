<?php

declare(strict_types=1);

namespace NIN\NationalIdentificationNumbers\Norway;

use DateTimeImmutable;
use NIN\NationalIdentificationNumbers\NationalIdentificationNumberInterface;

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
abstract class NorwayNationalIdentificationNumber implements NationalIdentificationNumberInterface
{
    public const COUNTRY_CODE = 'NO';

    public function __construct(DateTimeImmutable $dateTime, int $individualNumber)
    {
        $this->dateTime = $dateTime;
        $this->individualNumber = $individualNumber;
    }

    public static function calculateChecksumFromDateAndIndividualNumber(DateTimeImmutable $date, int $individualNumber, bool $isDNumber, bool $isHNumber): int
    {
        $day = (int)$date->format('d') + ($isDNumber ? 40 : 0);
        $month = (int)$date->format('m') + ($isHNumber ? 40 : 0);
        $numbers = sprintf('%02d%02d%02d%03d', $day, $month, (int)$date->format('y'), $individualNumber);

        $k1 = 11 - (((3 * $numbers[0]) + (7 * $numbers[1]) + (6 * $numbers[2]) + (1 * $numbers[3]) + (8 * $numbers[4]) + (9 * $numbers[5]) + (4 * $numbers[6]) + (5 * $numbers[7]) + (2 * $numbers[8])) % 11);
        $k2 = 11 - (((5 * $numbers[0]) + (4 * $numbers[1]) + (3 * $numbers[2]) + (2 * $numbers[3]) + (7 * $numbers[4]) + (6 * $numbers[5]) + (5 * $numbers[6]) + (4 * $numbers[7]) + (3 * $numbers[8]) + (2 * $k1)) % 11);

        $str = $k1 . $k2;
        return (int)$str;
    }

    final public function getCountryCode(): string
    {
        return self::COUNTRY_CODE;
    }

    /**
     * @var DateTimeImmutable
     */
    protected $dateTime;

    /**
     * @var int Three digit integer
     */
    protected $individualNumber;
}
