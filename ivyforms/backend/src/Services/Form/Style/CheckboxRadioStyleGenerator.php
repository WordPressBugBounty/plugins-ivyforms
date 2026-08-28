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
 * Class CheckboxRadioStyleGenerator
 *
 * Generates CSS for checkboxes and radio buttons
 *
 * @package IvyForms\Services\Form\StyleHelpers
 */
class CheckboxRadioStyleGenerator implements StyleGeneratorInterface
{
    /**
     * Generate checkbox and radio styles
     *
     * @param CssBuilder $css
     * @param StyleContext $context
     * @return void
     */
    public function generate(CssBuilder $css, StyleContext $context): void
    {
        $activeColors = $context->getActiveColors();
        if (empty($activeColors['checkboxRadioSelection'])) {
            return;
        }

        $borderColor = $activeColors['checkboxRadioBorder'] ?? $activeColors['checkboxRadioSelection'];
        $baseSelector = $context->getBaseSelector();

        $this->addCheckboxStyles($css, $baseSelector, $activeColors['checkboxRadioSelection'], $borderColor);
        $this->addRadioStyles($css, $baseSelector, $activeColors['checkboxRadioSelection'], $borderColor);
        $this->addLabelStyles($css, $baseSelector, $activeColors);
        $this->addRequiredIndicator($css, $baseSelector, $activeColors);
    }

    /**
     * Add checkbox styles
     *
     * @param CssBuilder $css
     * @param string $baseSelector
     * @param string $selectionColor
     * @param string $borderColor
     * @return void
     */
    private function addCheckboxStyles(
        CssBuilder $css,
        string $baseSelector,
        string $selectionColor,
        string $borderColor
    ): void {
        // Unchecked state
        $css->addRule(
            [
                $baseSelector . ' .ivyforms-checkbox .el-checkbox__input .el-checkbox__inner',
                $baseSelector . ' .el-checkbox__input .el-checkbox__inner',
            ],
            [
                '  border-color: ' . esc_attr($borderColor) . ' !important;',
                '  border-width: 2px !important;',
                '  border-radius: 4px !important;',
                '  background-color: transparent !important;',
                '  box-shadow: none !important;',
            ]
        );

        // Checked state
        $css->addRule(
            [
                $baseSelector . ' .ivyforms-checkbox .el-checkbox__input.is-checked .el-checkbox__inner',
                $baseSelector . ' .el-checkbox__input.is-checked .el-checkbox__inner',
            ],
            [
                '  background-color: ' . esc_attr($selectionColor) . ' !important;',
                '  border-color: ' . esc_attr($selectionColor) . ' !important;',
                '  box-shadow: none !important;',
            ]
        );
    }

    /**
     * Add radio button styles
     *
     * @param CssBuilder $css
     * @param string $baseSelector
     * @param string $selectionColor
     * @param string $borderColor
     * @return void
     */
    private function addRadioStyles(
        CssBuilder $css,
        string $baseSelector,
        string $selectionColor,
        string $borderColor
    ): void {
        // Unchecked state
        $css->addRule(
            [
                $baseSelector . ' .ivyforms-radio .el-radio__input .el-radio__inner',
                $baseSelector . ' .el-radio__input .el-radio__inner',
            ],
            [
                '  border-color: ' . esc_attr($borderColor) . ' !important;',
                '  border-width: 2px !important;',
                '  border-radius: 50% !important;',
                '  background-color: transparent !important;',
                '  box-shadow: none !important;',
            ]
        );

        // Checked state - outer ring
        $css->addRule(
            [
                $baseSelector . ' .ivyforms-radio .el-radio__input.is-checked .el-radio__inner',
                $baseSelector . ' .el-radio__input.is-checked .el-radio__inner',
            ],
            [
                '  border-color: ' . esc_attr($selectionColor) . ' !important;',
                '  background-color: transparent !important;',
                '  box-shadow: none !important;',
            ]
        );

        // Checked state - inner dot
        $css->addRule(
            [
                $baseSelector . ' .ivyforms-radio .el-radio__input.is-checked .el-radio__inner::after',
                $baseSelector . ' .el-radio__input.is-checked .el-radio__inner::after',
            ],
            [
                '  background-color: ' . esc_attr($selectionColor) . ' !important;',
                '  width: 8px !important;',
                '  height: 8px !important;',
            ]
        );
    }

    /**
     * Add label styles
     *
     * @param CssBuilder $css
     * @param string $baseSelector
     * @param array<string, string> $activeColors
     * @return void
     */
    private function addLabelStyles(
        CssBuilder $css,
        string $baseSelector,
        array $activeColors
    ): void {
        $labelColor = $activeColors['checkboxRadioLabel'] ?? $activeColors['fieldLabels'] ?? null;

        if (empty($labelColor)) {
            return;
        }

        $css->addRule(
            [
                $baseSelector . ' .ivyforms-checkbox .el-checkbox__label',
                $baseSelector . ' .el-checkbox__label',
            ],
            ['  color: ' . esc_attr($labelColor) . ' !important;']
        );

        $css->addRule(
            [
                $baseSelector . ' .ivyforms-radio .el-radio__label',
                $baseSelector . ' .el-radio__label',
            ],
            ['  color: ' . esc_attr($labelColor) . ' !important;']
        );
    }

    /**
     * Add required indicator styles
     *
     * @param CssBuilder $css
     * @param string $baseSelector
     * @param array<string, string> $activeColors
     * @return void
     */
    private function addRequiredIndicator(
        CssBuilder $css,
        string $baseSelector,
        array $activeColors
    ): void {
        if (empty($activeColors['requiredIndicator'])) {
            return;
        }

        $css->addRule(
            [
                $baseSelector . ' .ivyforms-form-item .ivyforms-form-item__asterisk',
                $baseSelector . ' .ivyforms-form-item__asterisk',
                $baseSelector . ' .el-form-item__label--required::before',
            ],
            ['  color: ' . esc_attr($activeColors['requiredIndicator']) . ' !important;']
        );
    }
}
