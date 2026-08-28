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
 * Class FieldBaseStylesApplier
 *
 * Applies base field styles such as wrapper, padding, labels, placeholders, and height overrides.
 *
 * @package IvyForms\Services\Form\StyleHelpers
 */
class FieldBaseStylesApplier
{
    /**
     * @var string[]
     */
    private const ALLOWED_ALIGNMENTS = ['left', 'center', 'right'];

    /**
     * Apply base field styles
     *
     * @param CssBuilder $css
     * @param string $baseSelector
     * @param array<string, string> $activeColors
     * @param array<string, string> $activeStyles
     * @return void
     */
    public function apply(
        CssBuilder $css,
        string $baseSelector,
        array $activeColors,
        array $activeStyles
    ): void {
        $activeCornerRadius = $activeStyles['fieldCornerRadius'] ?? null;

        $this->addFieldWrapperStyles($css, $baseSelector, $activeColors, $activeStyles, $activeCornerRadius);
        $this->addHeightOverrides($css, $baseSelector);
        $this->addPaddingStyles($css, $baseSelector, $activeStyles);
        $this->addLabelStyles($css, $baseSelector, $activeColors, $activeStyles);
        $this->addPlaceholderStyles($css, $baseSelector, $activeColors, $activeStyles);
        $this->addDescriptionStyles($css, $baseSelector, $activeColors);
    }

    /**
     * Add field wrapper styles
     *
     * @param CssBuilder $css
     * @param string $baseSelector
     * @param array<string, string> $activeColors
     * @param array<string, string> $activeStyles
     * @param string|null $activeCornerRadius
     * @return void
     */
    private function addFieldWrapperStyles(
        CssBuilder $css,
        string $baseSelector,
        array $activeColors,
        array $activeStyles,
        ?string $activeCornerRadius
    ): void {
        $selectors = $this->getFieldWrapperSelectors($baseSelector);
        $styles = [];

        $borderWidth = $activeStyles['fieldBorderWidth'] ?? '1px';
        $borderStyle = $activeStyles['fieldBorderStyle'] ?? 'solid';
        $styles[] = '  border-width: ' . esc_attr($borderWidth) . ' !important;';
        $styles[] = '  border-style: ' . esc_attr($borderStyle) . ' !important;';

        if (!empty($activeCornerRadius)) {
            $styles[] = '  border-radius: ' . esc_attr($activeCornerRadius) . ' !important;';
        }

        if (!empty($activeColors['fieldBorderColor'])) {
            $styles[] = '  border-color: ' . esc_attr($activeColors['fieldBorderColor']) . ' !important;';
        }

        if (!empty($activeColors['fieldText'])) {
            $styles[] = '  color: ' . esc_attr($activeColors['fieldText']) . ' !important;';
        }

        if (!empty($activeColors['fieldBackground'])) {
            $styles[] = '  background-color: ' . esc_attr($activeColors['fieldBackground']) . ' !important;';
        }

        $css->addRule($selectors, $styles);

        // Separate rule for phone field inner input text color (to avoid being overridden by default styles)
        if (!empty($activeColors['fieldText'])) {
            $phoneTextSelectors = [$baseSelector . ' .vti__input'];
            $css->addRule($phoneTextSelectors, [
                '  color: ' . esc_attr($activeColors['fieldText']) . ' !important;',
            ]);
        }
    }

    /**
     * Add height override styles
     *
     * @param CssBuilder $css
     * @param string $baseSelector
     * @return void
     */
    private function addHeightOverrides(CssBuilder $css, string $baseSelector): void
    {
        $heightSelectors = [
            $baseSelector . ' .ivyforms-input.el-input',
            $baseSelector . ' .ivyforms-phone-input',
            $baseSelector . ' .ivyforms-field__phone',
            $baseSelector . ' .ivyforms-field__select',
            $baseSelector . ' .ivyforms-field__address',
            $baseSelector . ' .ivyforms-field__date',
            $baseSelector . ' .ivyforms-field__time',
            $baseSelector . ' .ivyforms-form-item-select .el-select__wrapper',
            $baseSelector . ' .ivyforms-phone-input .vue-tel-input',
        ];

        $css->addRule($heightSelectors, [
            '  height: auto !important;',
            '  min-height: 40px !important;',
        ]);
    }

    /**
     * Add padding styles
     *
     * @param CssBuilder $css
     * @param string $baseSelector
     * @param array<string, string> $activeStyles
     * @return void
     */
    private function addPaddingStyles(
        CssBuilder $css,
        string $baseSelector,
        array $activeStyles
    ): void {
        $paddingY = $activeStyles['fieldPaddingTopBottom'] ?? '0px';
        $paddingX = $activeStyles['fieldPaddingLeftRight'] ?? '12px';

        $directInputSelectors = [
            $baseSelector . ' input[type="email"]',
            $baseSelector . ' input[type="tel"]',
            $baseSelector . ' input[type="number"]',
            $baseSelector . ' input[type="url"]',
            $baseSelector . ' input[type="date"]',
            $baseSelector . ' input[type="time"]',
            $baseSelector . ' textarea',
        ];

        $css->addRule($directInputSelectors, [
            '  padding: ' . esc_attr($paddingY) . ' ' . esc_attr($paddingX) . ' !important;',
        ]);
    }

    /**
     * Add label styles
     *
     * @param CssBuilder $css
     * @param string $baseSelector
     * @param array<string, string> $activeColors
     * @param array<string, string> $activeStyles
     * @return void
     */
    private function addLabelStyles(
        CssBuilder $css,
        string $baseSelector,
        array $activeColors,
        array $activeStyles
    ): void {
        if (!$this->hasAnyLabelStyle($activeColors, $activeStyles)) {
            return;
        }

        $styles = $this->buildLabelStyles($activeColors, $activeStyles);
        $css->addRule($this->getLabelSelectors($baseSelector), $styles);
    }

    /**
     * Check whether any label style value is present.
     *
     * @param array<string, string> $activeColors
     * @param array<string, string> $activeStyles
     * @return bool
     */
    private function hasAnyLabelStyle(array $activeColors, array $activeStyles): bool
    {
        return !empty($activeColors['fieldLabels'])
            || !empty($activeStyles['fieldLabelPaddingLeftRight'])
            || !empty($activeStyles['fieldLabelPaddingTopBottom'])
            || !empty($activeStyles['fieldLabelAlignment']);
    }

    /**
     * Build label styles list.
     *
     * @param array<string, string> $activeColors
     * @param array<string, string> $activeStyles
     * @return string[]
     */
    private function buildLabelStyles(array $activeColors, array $activeStyles): array
    {
        $styles = [];

        if (!empty($activeColors['fieldLabels'])) {
            $styles[] = '  color: ' . esc_attr($activeColors['fieldLabels']) . ' !important;';
        }

        if (!empty($activeStyles['fieldLabelPaddingLeftRight'])) {
            $styles[] = '  padding-left: ' . esc_attr($activeStyles['fieldLabelPaddingLeftRight']) . ' !important;';
            $styles[] = '  padding-right: ' . esc_attr($activeStyles['fieldLabelPaddingLeftRight']) . ' !important;';
        }

        if (!empty($activeStyles['fieldLabelPaddingTopBottom'])) {
            $styles[] = '  padding-top: ' . esc_attr($activeStyles['fieldLabelPaddingTopBottom']) . ' !important;';
            $styles[] = '  padding-bottom: ' . esc_attr($activeStyles['fieldLabelPaddingTopBottom']) . ' !important;';
        }

        $this->appendLabelAlignmentStyles($styles, $activeStyles['fieldLabelAlignment'] ?? null);

        return $styles;
    }

    /**
     * Add label alignment styles when alignment is valid.
     *
     * @param string[] $styles
     * @param mixed $labelAlignmentRaw
     * @return void
     */
    private function appendLabelAlignmentStyles(array &$styles, $labelAlignmentRaw): void
    {
        $labelAlignment = $this->sanitizeAlignment($labelAlignmentRaw);

        if (null === $labelAlignment) {
            return;
        }

        $justifyContent = $this->mapAlignmentToJustifyContent($labelAlignment);

        if ('left' === $labelAlignment) {
            return;
        }

        $styles[] = '  text-align: ' . esc_attr($labelAlignment) . ' !important;';
        $styles[] = '  justify-content: ' . esc_attr($justifyContent) . ' !important;';
        $styles[] = '  display: flex !important;';
        $styles[] = '  width: 100% !important;';
    }

    /**
     * Get label selectors.
     *
     * @param string $baseSelector
     * @return string[]
     */
    private function getLabelSelectors(string $baseSelector): array
    {
        return [
            $baseSelector . ' .ivyforms-form-item .el-form-item__label',
            $baseSelector . ' .ivyforms-form-item__left-label',
            $baseSelector . ' .ivyforms-label',
            $baseSelector . ' .el-form-item__label',
            $baseSelector . ' label',
        ];
    }

    /**
     * Add placeholder styles
     *
     * @param CssBuilder $css
     * @param string $baseSelector
     * @param array<string, string> $activeColors
     * @param array<string, string> $activeStyles
     * @return void
     */
    private function addPlaceholderStyles(
        CssBuilder $css,
        string $baseSelector,
        array $activeColors,
        array $activeStyles
    ): void {
        if (
            empty($activeColors['fieldPlaceholder']) &&
            empty($activeStyles['fieldPlaceholderPaddingLeftRight']) &&
            empty($activeStyles['fieldPlaceholderAlignment'])
        ) {
            return;
        }

        $placeholderSelectors = [
            $baseSelector . ' input::placeholder',
            $baseSelector . ' textarea::placeholder',
            $baseSelector . ' .el-input__inner::placeholder',
            $baseSelector . ' .el-textarea__inner::placeholder',
        ];

        $styles = [];

        if (!empty($activeColors['fieldPlaceholder'])) {
            $styles[] = '  color: ' . esc_attr($activeColors['fieldPlaceholder']) . ' !important;';
        }

        if (!empty($activeStyles['fieldPlaceholderPaddingLeftRight'])) {
            $styles[] = '  padding-left: ' .
                esc_attr($activeStyles['fieldPlaceholderPaddingLeftRight']) . ' !important;';
            $styles[] = '  padding-right: ' .
                esc_attr($activeStyles['fieldPlaceholderPaddingLeftRight']) . ' !important;';
        }

        $placeholderAlignment = $this->sanitizeAlignment($activeStyles['fieldPlaceholderAlignment'] ?? null);
        if (null !== $placeholderAlignment) {
            $styles[] = '  text-align: ' . esc_attr($placeholderAlignment) . ' !important;';
        }

        if (!empty($styles)) {
            $css->addRule($placeholderSelectors, $styles);
        }
    }

    /**
     * Add default field description styles.
     *
     * @param CssBuilder $css
     * @param string $baseSelector
     * @param array<string, string> $activeColors
     * @return void
     */
    private function addDescriptionStyles(
        CssBuilder $css,
        string $baseSelector,
        array $activeColors
    ): void {
        if (empty($activeColors['fieldDescriptionColor'])) {
            return;
        }

        $css->addRule([
            $baseSelector . ' .ivyforms-form-item__info-text',
        ], [
            '  color: ' . esc_attr($activeColors['fieldDescriptionColor']) . ' !important;',
            '  --ivyforms-info-color: ' . esc_attr($activeColors['fieldDescriptionColor']) . ' !important;',
        ]);
    }

    /**
     * Get field wrapper selectors
     *
     * @param string $baseSelector
     * @return string[]
     */
    private function getFieldWrapperSelectors(string $baseSelector): array
    {
        return [
            $baseSelector . ' .ivyforms-input.el-input .el-input__wrapper',
            $baseSelector . ' .ivyforms-field .el-input__wrapper',
            $baseSelector . ' .ivyforms-field .el-textarea__inner',
            $baseSelector . ' .el-select .el-input__wrapper',
            $baseSelector . ' .ivyforms-phone-input .el-input__wrapper',
            $baseSelector . ' .ivyforms-field__select .el-input__wrapper',
        ];
    }

    /**
     * Sanitize alignment value.
     *
     * @param mixed $alignment
     * @return string|null
     */
    private function sanitizeAlignment($alignment): ?string
    {
        if (!is_string($alignment) || '' === $alignment) {
            return null;
        }

        return in_array($alignment, self::ALLOWED_ALIGNMENTS, true) ? $alignment : null;
    }

    /**
     * Map alignment value to justify-content equivalent.
     *
     * @param string $alignment
     * @return string
     */
    private function mapAlignmentToJustifyContent(string $alignment): string
    {
        if ('left' === $alignment) {
            return 'flex-start';
        }

        return 'center' === $alignment ? 'center' : 'flex-end';
    }
}
