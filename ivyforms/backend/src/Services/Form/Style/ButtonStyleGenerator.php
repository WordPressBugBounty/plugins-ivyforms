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
 * Class ButtonStyleGenerator
 *
 * Generates CSS for form action buttons (submit row and multipage page navigation).
 *
 * @package IvyForms\Services\Form\StyleHelpers
 */
class ButtonStyleGenerator implements StyleGeneratorInterface
{
    /**
     * Generate button styles
     *
     * @param CssBuilder $css
     * @param StyleContext $context
     * @return void
     */
    public function generate(CssBuilder $css, StyleContext $context): void
    {
        $activeColors = $context->getActiveColors();
        $activeStyles = $context->getActiveStyles();

        if (!$this->hasAnyButtonStyles($activeColors, $activeStyles)) {
            return;
        }

        $selectors = $this->getButtonSelectors($context->getBaseSelector());
        $styles = $this->buildButtonStyles($activeColors, $activeStyles);

        $css->addRule($selectors, $styles);
        $this->addHoverState($css, $selectors);
    }

    /**
     * Build button styles
     *
     * @param array<string, string> $activeColors
     * @param array<string, string> $activeStyles
     * @return string[]
     */
    private function buildButtonStyles(array $activeColors, array $activeStyles): array
    {
        $styles = [];

        if (!empty($activeColors['buttonBackground'])) {
            $styles[] = '  background-color: ' . esc_attr($activeColors['buttonBackground']) . ' !important;';
        }

        if (!empty($activeColors['buttonText'])) {
            $styles[] = '  color: ' . esc_attr($activeColors['buttonText']) . ' !important;';
        }

        $this->addButtonBorder($styles, $activeColors, $activeStyles);
        $this->addButtonRadius($styles, $activeStyles);
        $this->addButtonPadding($styles, $activeStyles);

        if (!empty($activeStyles['buttonWidth'])) {
            $styles[] = '  width: ' . esc_attr($activeStyles['buttonWidth']) . ' !important;';
        }

        if (!empty($activeStyles['buttonHeight'])) {
            $styles[] = '  height: ' . esc_attr($activeStyles['buttonHeight']) . ' !important;';
        }

        $styles[] = '  min-width: 4.5rem !important;';
        $styles[] = '  min-height: 2.5rem !important;';

        $styles[] = '  cursor: pointer !important;';
        $styles[] = '  transition: all 0.2s ease !important;';

        return $styles;
    }

    /**
     * Add button padding
     *
     * @param string[] $styles
     * @param array<string, string> $activeStyles
     * @return void
     */
    private function addButtonPadding(array &$styles, array $activeStyles): void
    {
        $paddingLeftRight = $activeStyles['buttonPaddingLeftRight'] ?? null;
        $paddingTopBottom = $activeStyles['buttonPaddingTopBottom'] ?? null;

        if ($paddingLeftRight || $paddingTopBottom) {
            $paddingLeftRight = $paddingLeftRight ?? '0px';
            $paddingTopBottom = $paddingTopBottom ?? '0px';
            $styles[] = '  padding: ' . esc_attr($paddingTopBottom) . ' ' .
                esc_attr($paddingLeftRight) . ' !important;';
        }
    }

    /**
     * Add button border
     *
     * @param string[] $styles
     * @param array<string, string> $activeColors
     * @param array<string, string> $activeStyles
     * @return void
     */
    private function addButtonBorder(
        array &$styles,
        array $activeColors,
        array $activeStyles
    ): void {
        $borderWidth = $activeStyles['buttonBorderWidth'] ?? null;

        if (empty($borderWidth) || $borderWidth === '0px') {
            $styles[] = '  border: none !important;';
            return;
        }

        $borderColor = $activeColors['buttonBorderColor'] ?? $activeColors['buttonBackground'] ?? '#707883';
        $styles[] = '  border: ' . esc_attr($borderWidth) . ' solid ' . esc_attr($borderColor) . ' !important;';
    }

    /**
     * Add button border radius
     *
     * @param string[] $styles
     * @param array<string, string> $activeStyles
     * @return void
     */
    private function addButtonRadius(array &$styles, array $activeStyles): void
    {
        $radius = $activeStyles['buttonCornerRadius'] ?? '6px';
        $styles[] = '  border-radius: ' . esc_attr($radius) . ' !important;';
    }

    /**
     * Add hover state
     *
     * @param CssBuilder $css
     * @param string[] $selectors
     * @return void
     */
    private function addHoverState(CssBuilder $css, array $selectors): void
    {
        $hoverSelectors = array_map(fn($selector) => $selector . ':hover', $selectors);

        $css->addRule($hoverSelectors, [
            '  opacity: 0.9 !important;',
            '  transform: translateY(-1px) !important;',
        ]);
    }

    /**
     * Get button selectors
     *
     * @param string $baseSelector
     * @return string[]
     */
    private function getButtonSelectors(string $baseSelector): array
    {
        return [
            $baseSelector . ' .ivyforms-form-submit .ivyforms-button-action',
            $baseSelector . ' .ivyforms-form-submit button.ivyforms-button-action',
            $baseSelector . ' .ivyforms-form-page-navigation .ivyforms-button-action',
            $baseSelector . ' .ivyforms-form-page-navigation button.ivyforms-button-action',
        ];
    }

    /**
     * Determine whether any button style option is active.
     *
     * @param array<string, string> $activeColors
     * @param array<string, string> $activeStyles
     * @return bool
     */
    private function hasAnyButtonStyles(array $activeColors, array $activeStyles): bool
    {
        $colorKeys = [
            'buttonBackground',
            'buttonText',
            'buttonBorderColor',
        ];

        foreach ($colorKeys as $key) {
            if (!empty($activeColors[$key])) {
                return true;
            }
        }

        $styleKeys = [
            'buttonBorderWidth',
            'buttonPaddingLeftRight',
            'buttonPaddingTopBottom',
            'buttonWidth',
            'buttonHeight',
            'buttonCornerRadius',
        ];

        foreach ($styleKeys as $key) {
            if (!empty($activeStyles[$key])) {
                return true;
            }
        }

        return false;
    }
}
