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
 * Class FormTextStyleGenerator
 *
 * Generates CSS for form title and description
 *
 * @package IvyForms\Services\Form\StyleHelpers
 */
class FormTextStyleGenerator implements StyleGeneratorInterface
{
    /**
     * Generate form title and description styles
     *
     * @param CssBuilder $css
     * @param StyleContext $context
     * @return void
     */
    public function generate(CssBuilder $css, StyleContext $context): void
    {
        $this->addFormTitleStyles($css, $context);
        $this->addFormDescriptionStyles($css, $context);
    }

    /**
     * Add form title styles
     *
     * @param CssBuilder $css
     * @param StyleContext $context
     * @return void
     */
    private function addFormTitleStyles(CssBuilder $css, StyleContext $context): void
    {
        $styleSettings = $context->getStyleSettings();
        $advanced = $styleSettings['advancedMode'] ?? [];
        $titleData = $advanced['formTitle'] ?? ($styleSettings['formTitle'] ?? []);
        $activeColors = $context->getActiveColors();
        $activeStyles = $context->getActiveStyles();
        $styles = [];

        if (!empty($titleData['fontSize'])) {
            $styles[] = '  font-size: ' . esc_attr($titleData['fontSize']) . ';';
        }

        if (!empty($activeColors['formTitleColor'])) {
            $styles[] = '  color: ' . esc_attr($activeColors['formTitleColor']) . ' !important;';
        }

        if (!empty($titleData['fontWeight'])) {
            $styles[] = '  font-weight: ' . esc_attr($titleData['fontWeight']) . ';';
        }

        // Add padding styles
        $paddingLeftRight = $activeStyles['formTitlePaddingLeftRight'] ?? ($titleData['paddingLeftRight'] ?? null);
        if (!empty($paddingLeftRight)) {
            $styles[] = '  padding-left: ' . esc_attr($paddingLeftRight) . ';';
            $styles[] = '  padding-right: ' . esc_attr($paddingLeftRight) . ';';
        }

        $paddingTopBottom = $activeStyles['formTitlePaddingTopBottom'] ?? ($titleData['paddingTopBottom'] ?? null);
        if (!empty($paddingTopBottom)) {
            $styles[] = '  padding-top: ' . esc_attr($paddingTopBottom) . ';';
            $styles[] = '  padding-bottom: ' . esc_attr($paddingTopBottom) . ';';
        }

        if (!empty($styles)) {
            $selector = $context->getBaseSelector() . ' .ivyforms-form-title-' . $context->getFormId();
            $css->addRule($selector, $styles);
        }
    }

    /**
     * Add form description styles
     *
     * @param CssBuilder $css
     * @param StyleContext $context
     * @return void
     */
    private function addFormDescriptionStyles(CssBuilder $css, StyleContext $context): void
    {
        $styleSettings = $context->getStyleSettings();
        $advanced = $styleSettings['advancedMode'] ?? [];
        $descData = $advanced['formDescription'] ?? ($styleSettings['formDescription'] ?? []);
        $activeColors = $context->getActiveColors();
        $activeStyles = $context->getActiveStyles();
        $styles = [];

        if (!empty($descData['fontSize'])) {
            $styles[] = '  font-size: ' . esc_attr($descData['fontSize']) . ';';
        }

        if (!empty($activeColors['formDescriptionColor'])) {
            $styles[] = '  color: ' . esc_attr($activeColors['formDescriptionColor']) . ' !important;';
        }

        if (!empty($descData['fontWeight'])) {
            $styles[] = '  font-weight: ' . esc_attr($descData['fontWeight']) . ';';
        }

        // Add padding styles
        $paddingLeftRight = $activeStyles['formDescriptionPaddingLeftRight']
            ?? ($descData['paddingLeftRight'] ?? null);
        if (!empty($paddingLeftRight)) {
            $styles[] = '  padding-left: ' . esc_attr($paddingLeftRight) . ';';
            $styles[] = '  padding-right: ' . esc_attr($paddingLeftRight) . ';';
        }

        $paddingTopBottom = $activeStyles['formDescriptionPaddingTopBottom']
            ?? ($descData['paddingTopBottom'] ?? null);
        if (!empty($paddingTopBottom)) {
            $styles[] = '  padding-top: ' . esc_attr($paddingTopBottom) . ';';
            $styles[] = '  padding-bottom: ' . esc_attr($paddingTopBottom) . ';';
        }

        if (!empty($styles)) {
            $selector = $context->getBaseSelector() . ' .ivyforms-form-description-' . $context->getFormId();
            $css->addRule($selector, $styles);
        }
    }
}
