<?php

namespace NIN\NationalIdentificationNumbers\Iceland;

use DateTimeImmutable;
use NIN\NationalIdentificationNumbers\NationalIdentificationNumberInterface;

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

    public function __construct(DateTimeImmutable $date, int $individualNumber)
    {
        $this->date = $date;
        $this->individualNumber = $individualNumber;
    }

    public function getCountryCode(): string
    {
        return self::COUNTRY_CODE;
    }

    public function __toString(): string
    {
        $checksum = self::calculateChecksum($this->date, $this->individualNumber);
        $centuryDigit = self::getCenturyDigitFromDate($this->date);

        return sprintf('%02d%02d%02d%02d%d%d', (int)$this->date->format('d'), (int)$this->date->format('m'), (int)$this->date->format('y'), $this->individualNumber, $checksum, $centuryDigit);
    }

    public static function calculateChecksum(DateTimeImmutable $date, int $individualNumber): int
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

    public static function getCenturyDigitFromDate(DateTimeImmutable $date): int
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
