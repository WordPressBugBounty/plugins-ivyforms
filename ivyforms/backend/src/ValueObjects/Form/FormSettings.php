<?php

namespace IvyForms\ValueObjects\Form;

/**
 * Class FormSettings
 * Groups display and behavior settings for a form
 *
 * @package IvyForms\ValueObjects\Form
 */
final class FormSettings
{
    /**
     * @var bool
     */
    public bool $showTitle;

    /**
     * @var bool
     */
    public bool $showDescription;

    /**
     * @var bool
     */
    public bool $storeEntries;

    /**
     * FormSettings constructor.
     *
     * @param bool $showTitle
     * @param bool $showDescription
     * @param bool $storeEntries
     */
    public function __construct(
        bool $showTitle,
        bool $showDescription,
        bool $storeEntries
    ) {
        $this->showTitle       = $showTitle;
        $this->showDescription = $showDescription;
        $this->storeEntries    = $storeEntries;
    }

    /**
     * @return bool
     */
    public function isShowTitle(): bool
    {
        return $this->showTitle;
    }

    /**
     * @return bool
     */
    public function isShowDescription(): bool
    {
        return $this->showDescription;
    }

    /**
     * @return bool
     */
    public function isStoreEntries(): bool
    {
        return $this->storeEntries;
    }

    /**
     * Convert to array
     *
     * @return array<string, bool>
     */
    public function toArray(): array
    {
        return [
            'showTitle'       => $this->showTitle,
            'showDescription' => $this->showDescription,
            'storeEntries'    => $this->storeEntries,
        ];
    }
}
