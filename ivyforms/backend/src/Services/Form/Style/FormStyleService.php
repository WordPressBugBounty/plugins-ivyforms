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
 * Class FormStyleService
 *
 * Generates scoped CSS from form style settings
 *
 * @package IvyForms\Services\Form
 */
class FormStyleService
{
    /**
     * Generate scoped CSS for a form
     *
     * @param int $formId
     * @param array<string, mixed>|null $styleSettings
     * @param string|null $formType
     * @return string
     */
    public static function generateFormCSS(int $formId, ?array $styleSettings, ?string $formType = null): string
    {
        $resolvedCss = self::resolveFormCssOutput($formId, $styleSettings, $formType);
        if ($resolvedCss !== null) {
            return $resolvedCss;
        }

        return self::buildClassicFormCss($formId, $styleSettings);
    }

    /**
     * @param array<string, mixed>|null $styleSettings
     */
    private static function resolveFormCssOutput(int $formId, ?array $styleSettings, ?string $formType): ?string
    {
        if (empty($styleSettings)) {
            return '';
        }

        if (isset($styleSettings['stylesEnabled']) && $styleSettings['stylesEnabled'] === false) {
            return '';
        }

        $extensionCss = apply_filters(
            'ivyforms/form/style/generate_css',
            null,
            $formId,
            $styleSettings,
            $formType
        );

        if (is_string($extensionCss)) {
            return $extensionCss;
        }

        if (!empty($formType) && $formType !== 'classic') {
            return '';
        }

        return null;
    }

    /**
     * @param array<string, mixed> $styleSettings
     */
    private static function buildClassicFormCss(int $formId, array $styleSettings): string
    {
        $css = new CssBuilder();
        $context = self::buildContext($formId, $styleSettings);

        // Generate all CSS sections using factory
        $generators = StyleGeneratorFactory::createGenerators();

        foreach ($generators as $generator) {
            $generator->generate($css, $context);
        }

        $extraRules = apply_filters('ivyforms/style/filter/css_rules', [], [
            'baseSelector' => $context->getBaseSelector(),
            'styleSettings' => $styleSettings,
            'formPopperScopeClass' => 'ivyforms-form-popper-' . self::normalizeFormScopeToken((string) $formId),
        ]);

        if (is_array($extraRules) && $extraRules !== []) {
            $css->addLines($extraRules);
        }

        return $css->build();
    }

    /**
     * Normalize form ID for scoped CSS class names (mirrors frontend selectors helper).
     */
    private static function normalizeFormScopeToken(string $formId): string
    {
        $normalized = trim($formId);
        $normalized = preg_replace('/[^a-zA-Z0-9_-]+/', '-', $normalized) ?? '';
        $normalized = preg_replace('/-+/', '-', $normalized) ?? '';
        $normalized = trim($normalized, '-');

        return $normalized !== '' ? $normalized : 'default';
    }

    /**
     * Build the style context from form ID and settings
     *
     * @param int $formId
     * @param array<string, mixed> $styleSettings
     * @return StyleContext
     */
    private static function buildContext(int $formId, array $styleSettings): StyleContext
    {
        $selectors = self::buildSelectors($formId, $styleSettings);
        $parser = new StyleSettingsParser($styleSettings);

        return new StyleContext(
            $selectors['base'],
            $selectors['innerWrapper'],
            $selectors['form'],
            $formId,
            $parser->getActiveColors(),
            $parser->getActiveStyles(),
            $styleSettings
        );
    }

    /**
     * Build CSS selectors for the form
     *
     * @param int $formId
     * @param array<string, mixed> $styleSettings
     * @return array<string, string>
     */
    private static function buildSelectors(int $formId, array $styleSettings): array
    {
        $formWrapperSelector = '.ivyforms-form-wrapper-' . $formId;
        $innerWrapperSelector = '.ivyforms-form-wrapper.ivyforms-form-wrapper-' . $formId;
        $formSelector = '.ivyforms-form-' . $formId;
        $customClass = !empty($styleSettings['customCssClass'])
            ? '.' . $styleSettings['customCssClass']
            : '';

        $baseSelector = $formWrapperSelector;
        if ($customClass) {
            $baseSelector .= $customClass;
        }

        return [
            'base' => $baseSelector,
            'innerWrapper' => $innerWrapperSelector,
            'form' => $formSelector,
        ];
    }
}
