<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace IvyForms\ValueObjects\Field;

/**
 * Class TimeFieldGeneralSettings
 *
 * Encapsulates time-specific settings extracted from FieldGeneralSettings.
 */
final class TimeFieldGeneralSettings
{
    /**
     * @var string
     */
    private string $timeFieldType;

    /**
     * @var string
     */
    private string $timeFormat;

    /**
     * @var string|null
     */
    private ?string $minTimeValue = null;

    /**
     * @var string|null
     */
    private ?string $maxTimeValue = null;

    /**
     * @var bool
     */
    private bool $limitTimeRange = false;

    public function __construct(
        bool $limitTimeRange,
        string $timeFieldType = '',
        string $timeFormat = '',
        ?string $minTimeValue = null,
        ?string $maxTimeValue = null
    ) {
        $this->timeFieldType = $timeFieldType;
        $this->timeFormat = $timeFormat;
        $this->minTimeValue = $minTimeValue;
        $this->maxTimeValue = $maxTimeValue;
        $this->limitTimeRange = $limitTimeRange;
    }

    /**
     * Get timeFieldType.
     */
    public function getTimeFieldType(): string
    {
        return $this->timeFieldType;
    }

    /**
     * Set timeFieldType.
     */
    public function setTimeFieldType(string $timeFieldType): void
    {
        $this->timeFieldType = $timeFieldType;
    }

    /**
     * Get timeFormat.
     */
    public function getTimeFormat(): string
    {
        return $this->timeFormat;
    }

    /**
     * Set timeFormat.
     */
    public function setTimeFormat(string $timeFormat): void
    {
        $this->timeFormat = $timeFormat;
    }

    public function getMinTimeValue(): ?string
    {
        return $this->minTimeValue;
    }

    public function setMinTimeValue(?string $minTimeValue): void
    {
        $this->minTimeValue = $minTimeValue;
    }

    public function getMaxTimeValue(): ?string
    {
        return $this->maxTimeValue;
    }

    public function setMaxTimeValue(?string $maxTimeValue): void
    {
        $this->maxTimeValue = $maxTimeValue;
    }

    public function getLimitTimeRange(): bool
    {
        return $this->limitTimeRange;
    }

    public function setLimitTimeRange(bool $limitTimeRange): void
    {
        $this->limitTimeRange = $limitTimeRange;
    }

    /**
     * @return array<string, string|bool|null>
     */
    public function toArray(): array
    {
        return [
            'timeFieldType'  => $this->getTimeFieldType(),
            'timeFormat'     => $this->getTimeFormat(),
            'minTimeValue'   => $this->getMinTimeValue(),
            'maxTimeValue'   => $this->getMaxTimeValue(),
            'limitTimeRange' => $this->getLimitTimeRange(),
        ];
    }
}
