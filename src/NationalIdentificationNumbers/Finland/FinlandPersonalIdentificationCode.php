<?php

declare(strict_types=1);

namespace NIN\NationalIdentificationNumbers\Finland;

use DateTimeImmutable;
use NIN\NationalIdentificationNumbers\NationalIdentificationNumberInterface;

class FinlandPersonalIdentificationCode implements NationalIdentificationNumberInterface
{
    public const COUNTRY_CODE = 'FI';

    public function __construct(DateTimeImmutable $date, int $individualNumber)
    {
        $this->date = $date;
        $this->individualNumber = $individualNumber;
    }

    public function getCountryCode(): string
    {
        return self::COUNTRY_CODE;
    }

    // todo add once general structure is set in stone
    //public function isTemporary(): bool
    //{
    //    return $this->individualNumber >= 900;
    //}

    public function __toString(): string
    {
        $separator = $this->getSeparatorFromDate($this->date);
        $checksum = self::calculateChecksum($this->date, $this->individualNumber);

        return sprintf("%02d%02d%02d%s%03d%s", (int)$this->date->format('d'), (int)$this->date->format('m'), (int)$this->date->format('y'), $separator, $this->individualNumber, $checksum);
    }

    public static function calculateChecksum(DateTimeImmutable $date, int $individualNumber): string
    {
        $numberMap = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F', 'H', 'J', 'K', 'L', 'M', 'N', 'P', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y'];

        $number = (int)sprintf('%02d%02d%02d%03d', $date->format('d'), $date->format('m'), $date->format('y'), $individualNumber);

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

        return $separator;
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
