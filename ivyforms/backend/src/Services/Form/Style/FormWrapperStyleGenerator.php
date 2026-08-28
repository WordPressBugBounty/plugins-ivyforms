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
 * Class FormWrapperStyleGenerator
 *
 * Generates CSS for form wrapper
 *
 * @package IvyForms\Services\Form\StyleHelpers
 */
class FormWrapperStyleGenerator implements StyleGeneratorInterface
{
    /**
     * @var string[]
     */
    private const ALLOWED_ALIGNMENTS = ['left', 'center', 'right'];

    /**
     * Generate form wrapper styles
     *
     * @param CssBuilder $css
     * @param StyleContext $context
     * @return void
     */
    public function generate(CssBuilder $css, StyleContext $context): void
    {
        $wrapperStyles = $this->buildWrapperStyles(
            $context->getActiveColors(),
            $context->getActiveStyles(),
            $context->getStyleSettings()
        );

        if (!empty($wrapperStyles)) {
            $css->addRule($context->getBaseSelector(), $wrapperStyles);
        }

        $this->addInnerWrapperStyles($css, $context->getInnerWrapperSelector());
        $this->addFormElementStyles(
            $css,
            $context->getBaseSelector(),
            $context->getFormSelector(),
            $context->getActiveStyles(),
            $context->getStyleSettings()
        );
    }

    /**
     * Build wrapper styles array
     *
     * @param array<string, string> $activeColors
     * @param array<string, string> $activeStyles
     * @param array<string, mixed> $styleSettings
     * @return string[]
     */
    private function buildWrapperStyles(
        array $activeColors,
        array $activeStyles,
        array $styleSettings
    ): array {
        $styles = [];

        if (!empty($activeColors['formBackground'])) {
            $styles[] = '  --ivyforms-color-form-background: '
                . esc_attr($activeColors['formBackground']) . ';';
            $styles[] = '  background-color: ' . esc_attr($activeColors['formBackground']) . ';';
        }

        $hasFormPadding = array_key_exists('formPaddingTopBottom', $activeStyles)
            || array_key_exists('formPaddingLeftRight', $activeStyles);

        if ($hasFormPadding) {
            $paddingTopBottom = $activeStyles['formPaddingTopBottom'] ?? '0px';
            $paddingLeftRight = $activeStyles['formPaddingLeftRight'] ?? '0px';
            $styles[] = '  padding: ' . esc_attr($paddingTopBottom) . ' ' . esc_attr($paddingLeftRight) . ';';
        }

        // Add form alignment using flexbox
        $alignment = $this->getFormAlignment($styleSettings);
        if ('left' !== $alignment) {
            $styles[] = '  display: flex;';
            $styles[] = '  flex-direction: column;';
            $styles[] = '  align-items: ' . $this->mapAlignmentToFlexPosition($alignment) . ';';
        }

        self::addBorderStyles($styles, $activeColors, $activeStyles);
        self::addCornerRadiusStyles($styles, $activeStyles);

        return $styles;
    }

    /**
     * Add border styles
     *
     * @param string[] $styles
     * @param array<string, string> $activeColors
     * @param array<string, string> $activeStyles
     * @return void
     */
    private function addBorderStyles(
        array &$styles,
        array $activeColors,
        array $activeStyles
    ): void {
        $hasBorder = !empty($activeColors['formBorderColor']) || !empty($activeStyles['formBorderWidth']);

        if (!$hasBorder) {
            $styles[] = '  border: none;';
            return;
        }

        if (!empty($activeColors['formBorderColor'])) {
            $styles[] = '  border-color: ' . esc_attr($activeColors['formBorderColor']) . ' !important;';
        }

        if (!empty($activeStyles['formBorderWidth'])) {
            $styles[] = '  border-width: ' . esc_attr($activeStyles['formBorderWidth']) . ' !important;';
        }

        $borderStyle = $activeStyles['formBorderStyle'] ?? 'solid';
        $styles[] = '  border-style: ' . esc_attr($borderStyle) . ' !important;';
    }

    /**
     * Add corner radius styles
     *
     * @param string[] $styles
     * @param array<string, string> $activeStyles
     * @return void
     */
    private function addCornerRadiusStyles(array &$styles, array $activeStyles): void
    {
        $radius = $activeStyles['formCornerRadius'] ?? null;

        if (!empty($radius)) {
            $styles[] = '  border-radius: ' . esc_attr($radius) . ' !important;';
            return;
        }

        $styles[] = '  border-radius: 0 !important;';
    }

    /**
     * Add inner wrapper styles to prevent double borders
     *
     * @param CssBuilder $css
     * @param string $innerWrapperSelector
     * @return void
     */
    private function addInnerWrapperStyles(CssBuilder $css, string $innerWrapperSelector): void
    {
        $css->addRule($innerWrapperSelector, [
            '  border: none !important;',
            '  box-shadow: none !important;',
            '  background: transparent !important;',
            '  padding: 0 !important;',
        ]);
    }

    /**
     * Add form element styles
     *
     * @param CssBuilder $css
     * @param string $baseSelector
     * @param string $formSelector
     * @param array<string, string> $activeStyles
     * @param array<string, mixed> $styleSettings
     * @return void
     */
    private function addFormElementStyles(
        CssBuilder $css,
        string $baseSelector,
        string $formSelector,
        array $activeStyles,
        array $styleSettings
    ): void {
        $css->addRule($baseSelector . ' ' . $formSelector, [
            '  border: none !important;',
            '  border-radius: 0 !important;',
        ]);

        $formStyles = [
            '  border: none !important;',
            '  box-shadow: none !important;',
            '  background: transparent !important;',
            '  display: flex !important;',
            '  flex-direction: column !important;',
        ];

        // Add vertical and horizontal spacing via flexbox gap
        $verticalSpacing = $activeStyles['verticalSpacing'] ?? '16px';
        $horizontalSpacing = $this->getHorizontalSpacing($styleSettings);

        // gap property expects: vertical horizontal
        $formStyles[] = '  gap: ' . esc_attr($verticalSpacing) . ' ' . esc_attr($horizontalSpacing) . ' !important;';

        $css->addRule($baseSelector . ' form', $formStyles);
    }

    /**
     * Get form alignment from style settings
     *
     * @param array<string, mixed> $styleSettings
     * @return string
     */
    private function getFormAlignment(array $styleSettings): string
    {
        $advancedMode = $styleSettings['advancedMode'] ?? [];
        $general = $advancedMode['general'] ?? [];
        $alignment = $general['alignment'] ?? 'left';

        return in_array($alignment, self::ALLOWED_ALIGNMENTS, true) ? $alignment : 'left';
    }

    /**
     * Map alignment value to corresponding flex alignment value.
     *
     * @param string $alignment
     * @return string
     */
    private function mapAlignmentToFlexPosition(string $alignment): string
    {
        if ('left' === $alignment) {
            return 'flex-start';
        }

        return 'center' === $alignment ? 'center' : 'flex-end';
    }

    /**
     * Get horizontal spacing from style settings
     *
     * @param array<string, mixed> $styleSettings
     * @return string
     */
    private function getHorizontalSpacing(array $styleSettings): string
    {
        $advancedMode = $styleSettings['advancedMode'] ?? [];
        $general = $advancedMode['general'] ?? [];

        if (!empty($general['horizontalSpacing'])) {
            return $general['horizontalSpacing'];
        }

        $quickMode = $styleSettings['quickMode'] ?? [];
        return $quickMode['horizontalSpacing'] ?? '12px';
    }
}
