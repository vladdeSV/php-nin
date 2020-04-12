<?php

namespace NIN\NationalIdentificationNumbers;

use DateTimeImmutable;
use InvalidArgumentException;

/**
 * - Follows format "DDMMYYNNKC"
 *   - DD = day, two digit
 *   - MM = month, two digit
 *   - YY = year, two digit
 *   - NN = individual number, two digit
 *   - K = checksum, single digit
 *   - C = century digit
 * - Checksum studied from
 *   - https://www.skra.is/english/individuals/me-and-my-family/my-registration/id-numbers/
 *   - https://www.skra.is/thjonusta/einstaklingar/eg-i-thjodskra/um-kennitolur/
 */
class IcelandIdentificationNumber implements NationalIdentificationNumberInterface
{
    public const COUNTRY_CODE = 'IS';

    private const REGEX_IDENTIFICATION_NUMBER = /** @lang PhpRegExp */
        '/^'
        . '(?<DD>\d{2})'
        . '(?<MM>\d{2})'
        . '(?<YY>\d{2})'
        . '-?'
        . '(?<individualNumber>\d{2})'
        . '(?<checksum>\d)'
        . '(?<centuryDigit>[890])'
        . '$/';

    public function __construct(string $identificationNumber)
    {
        $matches = [];
        if (!preg_match(self::REGEX_IDENTIFICATION_NUMBER, $identificationNumber, $matches)) {
            throw new InvalidArgumentException('Invalid format.');
        }

        $YY = (int)$matches['YY'];
        $MM = (int)$matches['MM'];
        $DD = (int)$matches['DD'];
        $individualNumber = (int)$matches['individualNumber'];
        $checksum = (int)$matches['checksum'];
        $centuryDigit = (int)$matches['centuryDigit'];

        $year = $this->getCenturyFromCenturyDigit($centuryDigit) + $YY;

        if (!checkdate($MM, $DD, $year)) {
            throw new InvalidArgumentException("Invalid date.");
        }

        $date = DateTimeImmutable::createFromFormat('Y-m-d', "$year-$MM-$DD");

        if ($checksum !== $this->calculateChecksum($date, $individualNumber)) {
            throw new InvalidArgumentException('Invalid checksum.');
        }

        $this->date = $date;
        $this->individualNumber = $individualNumber;
    }

    public function getCountryCode(): string
    {
        return self::COUNTRY_CODE;
    }

    public function __toString(): string
    {
        $checksum = $this->calculateChecksum($this->date, $this->individualNumber);
        $centuryDigit = $this->getCenturyDigitFromDate($this->date);

        return sprintf('%02d%02d%02d%02d%d%d', (int)$this->date->format('d'), (int)$this->date->format('m'), (int)$this->date->format('y'), $this->individualNumber, $checksum, $centuryDigit);
    }

    private function getCenturyFromCenturyDigit(int $centuryDigit): int
    {
        $century = null;

        if ($centuryDigit === 8) {
            $century = 1800;
        } else if ($centuryDigit === 9) {
            $century = 1900;
        } else if ($centuryDigit === 0) {
            $century = 2000;
        }

        return $century;
    }

    private function calculateChecksum(DateTimeImmutable $date, int $individualNumber): int
    {
        $numbers = sprintf('%02d%02d%02d%02d', (int)$date->format('d'), (int)$date->format('m'), (int)$date->format('y'), $individualNumber);

        $sum = (
                3 * (int)($numbers[0]) +
                2 * (int)($numbers[1]) +
                7 * (int)($numbers[2]) +
                6 * (int)($numbers[3]) +
                5 * (int)($numbers[4]) +
                4 * (int)($numbers[5]) +
                3 * (int)($numbers[6]) +
                2 * (int)($numbers[7])
            ) % 11;

        if ($sum > 0) {
            $sum = 11 - $sum;
        }

        return $sum;
    }

    private function getCenturyDigitFromDate(DateTimeImmutable $date): int
    {
        $centuryDigit = null;

        $century = (int)((int)$date->format('Y') / 100);
        if ($century === 18) {
            $centuryDigit = 8;
        } else if ($century === 19) {
            $centuryDigit = 9;
        } else if ($century === 20) {
            $centuryDigit = 0;
        }

        return $centuryDigit;
    }

    /**
     * @var DateTimeImmutable
     */
    private $date;

    /**
     * @var int
     */
    private $individualNumber;
}
