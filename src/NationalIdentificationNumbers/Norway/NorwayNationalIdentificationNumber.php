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

    public final function getCountryCode(): string
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
