<?php

declare(strict_types=1);

namespace NIN\NationalIdentificationNumbers;

use DateTimeImmutable;
use InvalidArgumentException;

/**
 * - Follows format "DDMMYYCNNNK"
 *   - DD = day, two digit
 *   - MM = month, two digit
 *   - YY = year, two digit
 *   - C = separator, '+' for 1800s, '-' for 1900s, and 'A' for 2000s
 *   - NNN = individual number, three digit
 *   - K = checksum, single digit or uppercase letter
 * - Individual numbers are even for females and odd for males
 * - Checksum studied from
 *   - https://dvv.fi/en/personal-identity-code
 *   - https://en.wikipedia.org/wiki/National_identification_number#Denmark
 */
class FinlandPersonalIdentificationCode implements NationalIdentificationNumberInterface
{
    public const COUNTRY_CODE = 'FI';

    private const REGEX_PERSONAL_IDENTITY_CODE = /** @lang PhpRegExp */
        '/^'
        . '(?<DD>\d{2})'
        . '(?<MM>\d{2})'
        . '(?<YY>\d{2})'
        . '(?<separator>[-+A])'
        . '(?<individualNumber>\d{3})'
        . '(?<checksum>[\dA-Y])'
        . '$/';

    public function __construct(string $nationalIdentificationNumber)
    {
        $matches = [];
        if (!preg_match(self::REGEX_PERSONAL_IDENTITY_CODE, $nationalIdentificationNumber, $matches)) {
            throw new InvalidArgumentException('Invalid format.');
        }

        $twoDigitYear = (int)$matches['YY'];
        $month = (int)$matches['MM'];
        $day = (int)$matches['DD'];
        $separator = $matches['separator'];
        $individualNumber = (int)$matches['individualNumber'];
        $checksum = $matches['checksum'];
        $isTemporary = false;

        if ($individualNumber <= 1) {
            throw new InvalidArgumentException('Invalid individual number.');
        }

        if ($individualNumber >= 900) {
            $isTemporary = true;
        }

        $century = $this->getCenturyFromSeparator($separator);
        $year = $century + $twoDigitYear;

        if (!checkdate($month, $day, $year)) {
            throw new InvalidArgumentException("Invalid date. {$year}-{$month}-{$day} does not exist.");
        }

        $date = DateTimeImmutable::createFromFormat('Y-m-d', "$year-$month-$day");

        if (self::calculateChecksum($date, $individualNumber) !== $checksum) {
            throw new InvalidArgumentException("Invalid checksum.");
        }

        $this->date = $date;
        $this->individualNumber = $individualNumber;
        $this->isTemporary = $isTemporary;
    }

    public function getCountryCode(): string
    {
        return self::COUNTRY_CODE;
    }

    public function __toString(): string
    {
        $separator = $this->getSeparatorFromDate($this->date);
        $checksum = self::calculateChecksum($this->date, $this->individualNumber);

        return sprintf("%02d%02d%02d%s%03d%s", (int)$this->date->format('d'), (int)$this->date->format('m'), (int)$this->date->format('y'), $separator, $this->individualNumber, $checksum);
    }

    private static function calculateChecksum(DateTimeImmutable $date, int $individualNumber)
    {
        $numberMap = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F', 'H', 'J', 'K', 'L', 'M', 'N', 'P', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y'];

        $number = (int)(sprintf('%02d%02d%02d%03d', $date->format('d'), $date->format('m'), $date->format('y'), $individualNumber));

        return $numberMap[round(fmod($number / 31.0, 1) * 31)];
    }

    private function getSeparatorFromDate(DateTimeImmutable $date): string
    {
        $century = (int)((int)$date->format('Y') / 100);
        $separator = null;

        /** @noinspection PhpSwitchCaseWithoutDefaultBranchInspection Invalid dates are filtered on creation */
        switch ($century) {
            case 18:
                $separator = '+';
                break;
            case 19:
                $separator = '-';
                break;
            case 20:
                $separator = 'A';
                break;
        }

        assert($separator !== null);
        return $separator;
    }

    private function getCenturyFromSeparator($separator): int
    {
        $century = null;
        /** @noinspection PhpSwitchCaseWithoutDefaultBranchInspection RegExp ensures separator is either +, -, or A */
        switch ($separator) {
            case '+':
                $century = 1800;
                break;
            case '-':
                $century = 1900;
                break;
            case 'A':
                $century = 2000;
                break;
        }

        assert($century !== null);
        return $century;
    }

    /**
     * @var DateTimeImmutable
     */
    private $date;

    /**
     * @var int
     */
    private $individualNumber;

    /**
     * @var bool
     */
    private $isTemporary;
}
