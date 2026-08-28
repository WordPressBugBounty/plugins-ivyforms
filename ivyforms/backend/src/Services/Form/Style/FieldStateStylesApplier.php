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
 * Class FieldStateStylesApplier
 *
 * Applies state-based field styles (active, read-only, error).
 *
 * @package IvyForms\Services\Form\StyleHelpers
 */
class FieldStateStylesApplier
{
    /**
     * Apply field state styles
     *
     * @param CssBuilder $css
     * @param string $baseSelector
     * @param array<string, mixed> $styleSettings
     * @return void
     */
    public function apply(CssBuilder $css, string $baseSelector, array $styleSettings): void
    {
        $this->addActiveState($css, $baseSelector, $styleSettings);
        $this->addReadOnlyState($css, $baseSelector, $styleSettings);
        $this->addErrorState($css, $baseSelector, $styleSettings);
    }

    /**
     * Add active (focused) state
     *
     * @param CssBuilder $css
     * @param string $baseSelector
     * @param array<string, mixed> $styleSettings
     * @return void
     */
    private function addActiveState(
        CssBuilder $css,
        string $baseSelector,
        array $styleSettings
    ): void {
        $stateValues = $this->getFieldStateValues($styleSettings, 'active');
        $background = $stateValues['background'];
        $borderColor = $stateValues['borderColor'];
        $borderStyle = $stateValues['borderStyle'];
        $textColor = $stateValues['textColor'];
        $descriptionColor = $stateValues['descriptionColor'];

        if (
            !$this->hasAnyCoreStateValue($background, $borderColor, $textColor)
            && !$this->isSetValue($descriptionColor)
        ) {
            return;
        }

        $this->addBackgroundAndBorderRule(
            $css,
            $this->getActiveStateSelectors($baseSelector),
            $background,
            $borderColor,
            $borderStyle
        );

        $this->addSimpleColorRule(
            $css,
            [
                $baseSelector . ' .ivyforms-input.el-input:focus-within input',
                $baseSelector . ' .ivyforms-field:focus-within input',
                $baseSelector . ' .ivyforms-field:focus-within textarea',
                $baseSelector . ' .ivyforms-field:focus-within .vti__input',
                $baseSelector . ' .ivyforms-field:focus-within .el-input__inner',
                $baseSelector . ' .ivyforms-field:focus-within .el-textarea__inner',
            ],
            $textColor
        );

        $this->addDescriptionColorRule(
            $css,
            [
                $baseSelector . ' .ivyforms-form-item:focus-within .ivyforms-form-item__info-text',
                $baseSelector . ' .el-form-item:focus-within .ivyforms-form-item__info-text',
            ],
            $descriptionColor
        );
    }

    /**
     * Add readonly state
     *
     * @param CssBuilder $css
     * @param string $baseSelector
     * @param array<string, mixed> $styleSettings
     * @return void
     */
    private function addReadOnlyState(
        CssBuilder $css,
        string $baseSelector,
        array $styleSettings
    ): void {
        $stateValues = $this->getFieldStateValues($styleSettings, 'readOnly');
        $background = $stateValues['background'];
        $borderColor = $stateValues['borderColor'];
        $borderStyle = $stateValues['borderStyle'];
        $textColor = $stateValues['textColor'];
        $descriptionColor = $stateValues['descriptionColor'];

        $hasCoreStateValue = $this->hasAnyCoreStateValue($background, $borderColor, $textColor);

        if (!$hasCoreStateValue && !$this->isSetValue($descriptionColor)) {
            return;
        }

        if ($hasCoreStateValue && !$this->isSetValue($textColor)) {
            $placeholderColor = $styleSettings['advancedMode']['fieldPlaceholder']['fontColor'] ?? null;
            if ($this->isSetValue($placeholderColor)) {
                $textColor = $placeholderColor;
            }
        }

        $isReadOnlyBackgroundBorderApplied = $this->addBackgroundAndBorderRule(
            $css,
            $this->getReadOnlyStateSelectors($baseSelector),
            $background,
            $borderColor,
            $borderStyle
        );

        if ($isReadOnlyBackgroundBorderApplied) {
            $this->addBackgroundAndBorderRule(
                $css,
                [
                    $baseSelector . ' .ivyforms-field.is-readonly .vue-tel-input',
                    $baseSelector . ' .ivyforms-field.is-readonly .vti__input',
                ],
                $background,
                $borderColor,
                $borderStyle
            );
        }

        $this->addSimpleColorRule(
            $css,
            [
                $baseSelector . ' .ivyforms-field.is-readonly input',
                $baseSelector . ' .ivyforms-field.is-readonly textarea',
                $baseSelector . ' .ivyforms-field.is-readonly .el-input__inner',
                $baseSelector . ' .ivyforms-field.is-readonly .el-textarea__inner',
                $baseSelector . ' .ivyforms-field.is-readonly .vti__input',
                $baseSelector . ' .el-input.is-disabled .el-input__inner',
                $baseSelector . ' .el-textarea.is-disabled .el-textarea__inner',
            ],
            $textColor
        );

        $this->addDescriptionColorRule(
            $css,
            [
                $baseSelector . ' .ivyforms-field.is-readonly .ivyforms-form-item__info-text',
                $baseSelector . ' .ivyforms-field.is-disabled .ivyforms-form-item__info-text',
                $baseSelector . ' .ivyforms-field.is-readonly .el-form-item .ivyforms-form-item__info-text',
                $baseSelector . ' .ivyforms-field.is-disabled .el-form-item .ivyforms-form-item__info-text',
            ],
            $descriptionColor
        );
    }

    /**
     * Add error state
     *
     * @param CssBuilder $css
     * @param string $baseSelector
     * @param array<string, mixed> $styleSettings
     * @return void
     */
    private function addErrorState(
        CssBuilder $css,
        string $baseSelector,
        array $styleSettings
    ): void {
        $stateValues = $this->getFieldStateValues($styleSettings, 'error');
        $background = $stateValues['background'];
        $borderColor = $stateValues['borderColor'];
        $borderStyle = $stateValues['borderStyle'];
        $textColor = $stateValues['textColor'];
        $descriptionColor = $stateValues['descriptionColor'];

        if (
            !$this->hasAnyCoreStateValue($background, $borderColor, $textColor)
            && !$this->isSetValue($descriptionColor)
        ) {
            return;
        }

        $this->addBackgroundAndBorderRule(
            $css,
            [
                $baseSelector . ' .ivyforms-form-item.is-error .ivyforms-input.el-input .el-input__wrapper',
                $baseSelector . ' .ivyforms-form-item.is-error .ivyforms-field .el-input__wrapper',
                $baseSelector . ' .ivyforms-form-item.is-error .el-input__wrapper',
                $baseSelector . ' .ivyforms-form-item.is-error .el-textarea__inner',
                $baseSelector . ' .ivyforms-form-item.is-error .el-select__wrapper',
                $baseSelector . ' .ivyforms-form-item.is-error .vue-tel-input',
            ],
            $background,
            $borderColor,
            $borderStyle
        );

        if ($this->isSetValue($textColor)) {
            $this->addSimpleColorRule(
                $css,
                [
                $baseSelector . ' .ivyforms-form-item.is-error .ivyforms-input.el-input input',
                $baseSelector . ' .ivyforms-form-item.is-error .ivyforms-field input',
                $baseSelector . ' .ivyforms-form-item.is-error input',
                $baseSelector . ' .ivyforms-form-item.is-error textarea',
                $baseSelector . ' .ivyforms-form-item.is-error .el-input__inner',
                $baseSelector . ' .ivyforms-form-item.is-error .el-textarea__inner',
                $baseSelector . ' .ivyforms-form-item.is-error .vti__input',
                $baseSelector . ' .ivyforms-form-item.is-error .el-input__prefix',
                $baseSelector . ' .ivyforms-form-item.is-error .el-input__suffix',
                $baseSelector . ' .ivyforms-form-item.is-error .el-input__prefix .el-icon',
                $baseSelector . ' .ivyforms-form-item.is-error .el-input__suffix .el-icon',
                ],
                $textColor
            );

            // Override icon color via CSS variable
            $errorIconSelectors = [$baseSelector . ' .ivyforms-form-item.is-error .ivyforms-icon'];
            $iconStyles = ['  --map-status-error-symbol-0: ' . esc_attr($textColor) . ' !important;'];
            $css->addRule($errorIconSelectors, $iconStyles);

            // Override SVG fill colors directly
            $errorSvgSelectors = [$baseSelector . ' .ivyforms-form-item.is-error .ivyforms-icon svg path'];
            $svgStyles = ['  fill: ' . esc_attr($textColor) . ' !important;'];
            $css->addRule($errorSvgSelectors, $svgStyles);
        }

        $this->addDescriptionColorRule(
            $css,
            [
                $baseSelector . ' .ivyforms-form-item.is-error .ivyforms-form-item__info-text',
                $baseSelector . ' .el-form-item.is-error .ivyforms-form-item__info-text',
            ],
            $descriptionColor
        );
    }

    /**
     * Check if any core state value exists (background, border, text).
     *
     * @param mixed $background
     * @param mixed $borderColor
     * @param mixed $textColor
     * @return bool
     */
    private function hasAnyCoreStateValue($background, $borderColor, $textColor): bool
    {
        return $this->isSetValue($background)
            || $this->isSetValue($borderColor)
            || $this->isSetValue($textColor);
    }

    /**
     * Get normalized field state values.
     *
     * @param array<string, mixed> $styleSettings
     * @param string $stateName
     * @return array<string, mixed>
     */
    private function getFieldStateValues(array $styleSettings, string $stateName): array
    {
        $stateSettings = $styleSettings['advancedMode']['fieldStates'][$stateName] ?? [];

        return [
            'background' => $stateSettings['background'] ?? null,
            'borderColor' => $stateSettings['borderColor'] ?? null,
            'borderStyle' => $stateSettings['borderStyle'] ?? 'solid',
            'textColor' => $stateSettings['textColor'] ?? null,
            'descriptionColor' => $stateSettings['descriptionColor'] ?? null,
        ];
    }

    /**
     * Check whether a value is considered set.
     *
     * @param mixed $value
     * @return bool
     */
    private function isSetValue($value): bool
    {
        return !empty($value);
    }

    /**
     * Add a background and border state rule.
     *
     * @param CssBuilder $css
     * @param string[] $selectors
     * @param mixed $background
     * @param mixed $borderColor
     * @param mixed $borderStyle
     * @return bool
     */
    private function addBackgroundAndBorderRule(
        CssBuilder $css,
        array $selectors,
        $background,
        $borderColor,
        $borderStyle
    ): bool {
        if (!$this->isSetValue($background) && !$this->isSetValue($borderColor)) {
            return false;
        }

        $styles = [];

        if ($this->isSetValue($background)) {
            $styles[] = '  background-color: ' . esc_attr($background) . ' !important;';
        }

        if ($this->isSetValue($borderColor)) {
            $styles[] = '  border-color: ' . esc_attr($borderColor) . ' !important;';
            $styles[] = '  border-style: ' . esc_attr($borderStyle) . ' !important;';
        }

        $css->addRule($selectors, $styles);

        return true;
    }

    /**
     * Add a simple text color rule if color is set.
     *
     * @param CssBuilder $css
     * @param string[] $selectors
     * @param mixed $color
     * @return void
     */
    private function addSimpleColorRule(CssBuilder $css, array $selectors, $color): void
    {
        if (!$this->isSetValue($color)) {
            return;
        }

        $css->addRule($selectors, ['  color: ' . esc_attr($color) . ' !important;']);
    }

    /**
     * Add description color and corresponding CSS variable if color is set.
     *
     * @param CssBuilder $css
     * @param string[] $selectors
     * @param mixed $descriptionColor
     * @return void
     */
    private function addDescriptionColorRule(
        CssBuilder $css,
        array $selectors,
        $descriptionColor
    ): void {
        if (!$this->isSetValue($descriptionColor)) {
            return;
        }

        $css->addRule($selectors, [
            '  color: ' . esc_attr($descriptionColor) . ' !important;',
            '  --ivyforms-info-color: ' . esc_attr($descriptionColor) . ' !important;',
        ]);
    }

    /**
     * Get active state selectors
     *
     * @param string $baseSelector
     * @return string[]
     */
    private function getActiveStateSelectors(string $baseSelector): array
    {
        return [
            $baseSelector . ' .ivyforms-input.el-input:focus-within .el-input__wrapper',
            $baseSelector . ' .ivyforms-field__text:focus-within .el-input__wrapper',
            $baseSelector . ' .ivyforms-field__email:focus-within .el-input__wrapper',
            $baseSelector . ' .ivyforms-field__textarea:focus-within .el-textarea__inner',
            $baseSelector . ' .ivyforms-field__select:focus-within .el-select__wrapper',
        ];
    }

    /**
     * Get readonly state selectors
     *
     * @param string $baseSelector
     * @return string[]
     */
    private function getReadOnlyStateSelectors(string $baseSelector): array
    {
        return [
            $baseSelector . ' .ivyforms-input.el-input.is-disabled .el-input__wrapper',
            $baseSelector . ' .ivyforms-field.is-disabled .el-input__wrapper',
            $baseSelector . ' .ivyforms-field.is-readonly .el-input__wrapper',
            $baseSelector . ' .ivyforms-field.is-disabled .el-textarea__inner',
        ];
    }
}
