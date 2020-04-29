<?php
declare(strict_types=1);

namespace NIN\NationalIdentificationNumbers\Denmark;

use DateTimeImmutable;
use NIN\NationalIdentificationNumbers\NationalIdentificationNumberInterface;

class DenmarkPersonalIdentificationNumber implements NationalIdentificationNumberInterface
{
    public const COUNTRY_CODE = 'DK';

    public function __construct(DateTimeImmutable $date, int $serialNumber)
    {
        $this->date = $date;
        $this->serialNumber = $serialNumber;
    }

    public function getCountryCode(): string
    {
        return self::COUNTRY_CODE;
    }

    public function __toString(): string
    {
        return $this->date->format('dmy') . '-' . $this->serialNumber;
    }

    /**
     * @var DateTimeImmutable
     */
    private $date;

    /**
     * @var int
     */
    private $serialNumber;
}