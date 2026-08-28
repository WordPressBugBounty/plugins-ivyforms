<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace IvyForms\Services\Form\Style;

// phpcs:disable PSR1.Files.SideEffects

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Class StyleContext
 *
 * Value object holding all data that style generators may need.
 * Generators read only the fields they require.
 *
 * @package IvyForms\Common\Helpers\StyleHelpers
 */
final class StyleContext
{
    /**
     * Base CSS selector (e.g. ".ivyforms-form-wrapper-5.custom-class")
     *
     * @var string
     */
    private string $baseSelector;

    /**
     * Inner wrapper CSS selector (e.g. ".ivyforms-form-wrapper.ivyforms-form-wrapper-5")
     *
     * @var string
     */
    private string $innerWrapperSelector;

    /**
     * Form element CSS selector (e.g. ".ivyforms-form-5")
     *
     * @var string
     */
    private string $formSelector;

    /**
     * Form ID for the form being styled
     *
     * @var int
     */
    private int $formId;

    /**
     * Active colors extracted from style settings
     *
     * @var array<string, string>
     */
    private array $activeColors;

    /**
     * Active styles extracted from style settings
     *
     * @var array<string, string>
     */
    private array $activeStyles;

    /**
     * Raw style settings array
     *
     * @var array<string, mixed>
     */
    private array $styleSettings;

    /**
     * Constructor
     *
     * @param string $baseSelector
     * @param string $innerWrapperSelector
     * @param string $formSelector
     * @param int $formId
     * @param array<string, string> $activeColors
     * @param array<string, string> $activeStyles
     * @param array<string, mixed> $styleSettings
     */
    public function __construct(
        string $baseSelector,
        string $innerWrapperSelector,
        string $formSelector,
        int $formId,
        array $activeColors,
        array $activeStyles,
        array $styleSettings
    ) {
        $this->baseSelector = $baseSelector;
        $this->innerWrapperSelector = $innerWrapperSelector;
        $this->formSelector = $formSelector;
        $this->formId = $formId;
        $this->activeColors = $activeColors;
        $this->activeStyles = $activeStyles;
        $this->styleSettings = $styleSettings;
    }

    /**
     * Get base CSS selector
     *
     * @return string
     */
    public function getBaseSelector(): string
    {
        return $this->baseSelector;
    }

    /**
     * Get inner wrapper CSS selector
     *
     * @return string
     */
    public function getInnerWrapperSelector(): string
    {
        return $this->innerWrapperSelector;
    }

    /**
     * Get form element CSS selector
     *
     * @return string
     */
    public function getFormSelector(): string
    {
        return $this->formSelector;
    }

    /**
     * Get form ID
     *
     * @return int
     */
    public function getFormId(): int
    {
        return $this->formId;
    }

    /**
     * Get active colors
     *
     * @return array<string, string>
     */
    public function getActiveColors(): array
    {
        return $this->activeColors;
    }

    /**
     * Get active styles
     *
     * @return array<string, string>
     */
    public function getActiveStyles(): array
    {
        return $this->activeStyles;
    }

    /**
     * Get raw style settings
     *
     * @return array<string, mixed>
     */
    public function getStyleSettings(): array
    {
        return $this->styleSettings;
    }
}
