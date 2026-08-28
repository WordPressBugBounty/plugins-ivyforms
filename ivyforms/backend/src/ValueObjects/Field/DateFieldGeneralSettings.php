<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace IvyForms\ValueObjects\Field;

/**
 * Class DateFieldGeneralSettings
 *
 * Encapsulates date-specific settings extracted from FieldGeneralSettings.
 */
final class DateFieldGeneralSettings
{
    /**
     * @var string
     */
    private string $dateFieldType;

    /**
     * @var string
     */
    private string $dateFormat;

    /**
     * @var string|null
     */
    private ?string $minDateValue;

    /**
     * @var string|null
     */
    private ?string $maxDateValue;
    /**
     * @var bool
     */
    private bool $limitDateRange;

    public function __construct(
        bool $limitDateRange,
        string $dateFieldType = '',
        string $dateFormat = '',
        ?string $minDateValue = null,
        ?string $maxDateValue = null
    ) {
        $this->limitDateRange = $limitDateRange;
        $this->dateFieldType = $dateFieldType;
        $this->dateFormat = $dateFormat;
        $this->minDateValue = $minDateValue;
        $this->maxDateValue = $maxDateValue;
    }

    /**
     * Get dateFieldType.
     */
    public function getDateFieldType(): string
    {
        return $this->dateFieldType;
    }

    /**
     * Set dateFieldType.
     */
    public function setDateFieldType(string $dateFieldType): void
    {
        $this->dateFieldType = $dateFieldType;
    }

    /**
     * Get dateFormat.
     */
    public function getDateFormat(): string
    {
        return $this->dateFormat;
    }

    /**
     * Set dateFormat.
     */
    public function setDateFormat(string $dateFormat): void
    {
        $this->dateFormat = $dateFormat;
    }

    /**
     * Get minDateValue.
     */
    public function getMinDateValue(): ?string
    {
        return $this->minDateValue;
    }

    /**
     * Set minDateValue.
     */
    public function setMinDateValue(?string $minDateValue): void
    {
        $this->minDateValue = $minDateValue;
    }

    /**
     * Get maxDateValue.
     */
    public function getMaxDateValue(): ?string
    {
        return $this->maxDateValue;
    }

    /**
     * Set maxDateValue.
     */
    public function setMaxDateValue(?string $maxDateValue): void
    {
        $this->maxDateValue = $maxDateValue;
    }

    /**
     * @return bool
     */
    public function isLimitDateRange(): bool
    {
        return $this->limitDateRange;
    }

    /**
     * @param bool $limitDateRange
     */
    public function setLimitDateRange(bool $limitDateRange): void
    {
        $this->limitDateRange = $limitDateRange;
    }

    /**
     * @return array<string, string|bool|null>
     */
    public function toArray(): array
    {
        return [
            'dateFieldType'  => $this->getDateFieldType(),
            'dateFormat'     => $this->getDateFormat(),
            'minDateValue'   => $this->getMinDateValue(),
            'maxDateValue'   => $this->getMaxDateValue(),
            'limitDateRange' => $this->isLimitDateRange(),
        ];
    }
}
