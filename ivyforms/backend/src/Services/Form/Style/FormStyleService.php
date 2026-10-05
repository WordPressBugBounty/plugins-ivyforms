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
            return self::generateResetCSS($formId, $styleSettings);
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
     * Generate a scoped reset stylesheet for forms with styles disabled (Approach A).
     *
     * Strips IvyForms base SCSS + the global Element Plus chrome down to a neutral
     * baseline so the host WordPress theme's typography/colors cascade in. Scoped to
     * the form wrapper and the form's popper class so it never affects the rest of
     * the page. Mirrors the frontend generator in
     * frontend/src/utils/form-styles/resetStyles.ts — keep the two in sync.
     *
     * @param int $formId
     * @param array<string, mixed> $styleSettings
     * @return string
     */
    private static function generateResetCSS(int $formId, array $styleSettings): string
    {
        $selectors  = self::buildSelectors($formId, $styleSettings);
        $base       = $selectors['base'];
        $popper     = 'ivyforms-form-popper-' . self::normalizeFormScopeToken((string) $formId);

        $css = new CssBuilder();

        // 0. Neutralize the brand/accent design tokens so no Ivy purple/teal remains
        //    on checkboxes, radios, selects, date pickers, tags, rich-text tabs, etc.
        //    Applied to the form wrapper AND the form's popper scope (poppers render at
        //    <body> level, outside the wrapper).
        $tokenLines = self::buildNeutralTokenDeclarations();
        $css->addRule($base, $tokenLines);
        $css->addRule('.' . $popper, $tokenLines);

        // 1. Typography inheritance — inherited props walk up to the theme.
        $css->addRule([
            $base,
            $base . ' label',
            $base . ' input',
            $base . ' textarea',
            $base . ' .el-input__inner',
            $base . ' .el-textarea__inner',
            $base . ' .el-select__placeholder',
            $base . ' .el-select__selected-item',
            $base . ' .el-select__selection',
            $base . ' .vti__input',
            // Labels — force every label-bearing element to the theme color so
            // labels are uniform across simple, composite (Name/Address) and
            // special fields. Some fall through to EP's grey `.el-form-item__label`
            // or Ivy's dark navy `--map-base-text-0`; both are overridden here.
            $base . ' .el-form-item__label',
            $base . ' .ivyforms-form-item__label',
            $base . ' .ivyforms-form-item__left-label',
            $base . ' .ivyforms-form-item__right-label',
            $base . ' .ivyforms-form-item__positioned-right-label',
            $base . ' .ivyforms-form-item__info-text',
            $base . ' .ivyforms-name-field__label',
            $base . ' .ivyforms-name-field__layout__field-wrapper__sublabel',
            $base . ' .ivyforms-field__address-label',
            $base . ' .el-checkbox__label',
            $base . ' .el-radio__label',
        ], [
            '  font-family: inherit !important;',
            '  font-size: inherit !important;',
            '  font-weight: inherit !important;',
            '  font-style: inherit !important;',
            '  line-height: inherit !important;',
            '  letter-spacing: inherit !important;',
            '  color: inherit !important;',
        ]);

        // 2. Field container chrome — strip EP + IvyForms boxing (incl. composite fields).
        $css->addRule([
            $base . ' .el-input__wrapper',
            $base . ' .el-select__wrapper',
            $base . ' .el-textarea__inner',
            $base . ' .el-input-number',
            $base . ' .ivyforms-text-number',
            $base . ' .ivyforms-text-number__input',
            $base . ' .vue-tel-input',
            $base . ' .vti__dropdown',
            $base . ' .choices__inner',
        ], [
            '  box-shadow: none !important;',
            '  border: 0 !important;',
            '  background: transparent !important;',
            '  border-radius: 0 !important;',
            '  padding: 0 !important;',
            '  min-height: 0 !important;',
            '  height: auto !important;',
            '  overflow: visible !important;',
        ]);

        // 3. Inner inputs — remove residual borders/backgrounds (no double box).
        $css->addRule([
            $base . ' .el-input__inner',
            $base . ' .el-textarea__inner',
            $base . ' .el-select__input',
            $base . ' .el-select__selection',
            $base . ' .vti__input',
        ], [
            '  box-shadow: none !important;',
            '  border: 0 !important;',
            '  background: transparent !important;',
            '  padding: 0 !important;',
            '  margin: 0 !important;',
            '  height: auto !important;',
            '  min-height: 0 !important;',
        ]);

        // 3b. Release EP fixed-height pins on wrappers/containers.
        $css->addRule([
            $base . ' .el-input',
            $base . ' .el-select',
            $base . ' .el-date-editor',
            $base . ' .ivyforms-input',
            $base . ' .ivyforms-phone-input',
            $base . ' .ivyforms-field__phone',
            $base . ' .ivyforms-field__select',
            $base . ' .ivyforms-field__address',
            $base . ' .ivyforms-field__date',
            $base . ' .ivyforms-field__time',
        ], [
            '  height: auto !important;',
            '  min-height: 0 !important;',
        ]);

        // 4. Buttons — neutral, native-looking button; font inherits the theme.
        $buttonTargets = [
            $base . ' .ivyforms-form-submit .ivyforms-button-action',
            $base . ' .ivyforms-form-submit button.ivyforms-button-action',
            $base . ' .ivyforms-form-page-navigation .ivyforms-button-action',
            $base . ' .ivyforms-form-page-navigation button.ivyforms-button-action',
        ];
        $css->addRule($buttonTargets, [
            '  background: revert !important;',
            '  color: revert !important;',
            '  border: revert !important;',
            '  border-radius: 0 !important;',
            '  box-shadow: none !important;',
            '  padding: revert !important;',
            '  width: revert !important;',
            '  height: revert !important;',
            '  min-width: 0 !important;',
            '  min-height: 0 !important;',
            '  font-family: inherit !important;',
            '  font-size: inherit !important;',
        ]);
        $css->addRule(array_map(static function (string $selector): string {
            return $selector . ':hover';
        }, $buttonTargets), [
            '  opacity: 1 !important;',
            '  transform: none !important;',
        ]);

        // 4b. Slider — IvySlider's own custom props come from the neutral "dusk"
        //     token family, so redefine them to the theme color (progress + thumb
        //     become currentColor, empty runway a faint tint). !important guards
        //     against the component's scoped custom-property definitions.
        $css->addRule($base . ' .ivyforms-slider', [
            '  --ivy-slider-primary: currentColor !important;',
            '  --ivy-slider-track-bg: color-mix(in srgb, currentColor 20%, transparent) !important;',
            '  --ivy-slider-text: currentColor !important;',
        ]);

        // 5. Poppers render at <body> level; keep EP defaults, just realign the font.
        $css->addRule([
            '.' . $popper,
            '.' . $popper . ' *',
        ], [
            '  font-family: inherit !important;',
        ]);

        return $css->build();
    }

    /**
     * Build the custom-property declarations that neutralize the IvyForms brand
     * accent (mirrors frontend resetStyles.ts buildNeutralTokenDeclarations).
     * `!important` is required — the :root.wcag-compliant token definitions
     * out-specify a single wrapper class.
     *
     * @return string[]
     */
    private static function buildNeutralTokenDeclarations(): array
    {
        // Neutral replacements for the IvyForms brand/accent tokens. The goal is
        // "don't style anything ourselves — match the host theme", so solids
        // resolve to `currentColor` (the element's inherited theme text color,
        // which self-adjusts to light/dark) and alpha tokens are a faint tint of
        // that same color via color-mix(). If color-mix() is unsupported the
        // declaration is invalid-at-computed-value-time and falls back to
        // transparent — never back to the Ivy purple.
        $neutralSolid = 'currentColor';
        $neutralAlpha = 'color-mix(in srgb, currentColor 12%, transparent)';

        // Translucent tints for the Element Plus primary "light" ramp.
        $elPrimaryLight = [
            '3' => 'color-mix(in srgb, currentColor 40%, transparent)',
            '5' => 'color-mix(in srgb, currentColor 30%, transparent)',
            '7' => 'color-mix(in srgb, currentColor 20%, transparent)',
            '8' => 'color-mix(in srgb, currentColor 12%, transparent)',
            '9' => 'color-mix(in srgb, currentColor 8%, transparent)',
        ];

        $families     = ['brand', 'purple'];
        $fillLevels   = ['4', '3', '2', '1', '0', '-1', '-2', '-3', '-4'];
        $alphaLevels  = ['o90', 'o80', 'o70', 'o60', 'o50', 'o40', 'o30', 'o20', 'o10', 'o05'];
        $strokeLevels = ['2', '1', '0', '-1', '-2'];
        $symbolLevels = ['2', '1', '0', '-1'];

        $lines = [];
        foreach ($families as $family) {
            foreach ($fillLevels as $level) {
                $lines[] = '  --map-base-' . $family . '-fill-' . $level . ': ' . $neutralSolid . ' !important;';
            }
            foreach ($alphaLevels as $level) {
                $lines[] = '  --map-base-' . $family . '-' . $level . ': ' . $neutralAlpha . ' !important;';
            }
            foreach ($strokeLevels as $level) {
                $lines[] = '  --map-base-' . $family . '-stroke-' . $level . ': ' . $neutralSolid . ' !important;';
            }
            foreach ($symbolLevels as $level) {
                $lines[] = '  --map-base-' . $family . '-symbol-' . $level . ': ' . $neutralSolid . ' !important;';
            }
        }

        // Plain brand token (IvyTimePicker maps --el-color-primary from this).
        $lines[] = '  --map-base-brand-0: ' . $neutralSolid . ' !important;';

        // Element Plus primary — drives default focus rings and picker highlights.
        $lines[] = '  --el-color-primary: ' . $neutralSolid . ' !important;';
        $lines[] = '  --el-color-primary-light-3: ' . $elPrimaryLight['3'] . ' !important;';
        $lines[] = '  --el-color-primary-light-5: ' . $elPrimaryLight['5'] . ' !important;';
        $lines[] = '  --el-color-primary-light-7: ' . $elPrimaryLight['7'] . ' !important;';
        $lines[] = '  --el-color-primary-light-8: ' . $elPrimaryLight['8'] . ' !important;';
        $lines[] = '  --el-color-primary-light-9: ' . $elPrimaryLight['9'] . ' !important;';
        $lines[] = '  --el-color-primary-dark-2: ' . $neutralSolid . ' !important;';

        return $lines;
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
        $parsedCustomClasses = self::parseCssClassList($styleSettings['customCssClass'] ?? '');
        $customClassName = $parsedCustomClasses[0] ?? '';
        $customClass = $customClassName !== '' ? '.' . $customClassName : '';

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

    /**
     * Parse a CSS class list with the same semantics as the frontend class parser.
     *
     * Splits on commas and whitespace, keeps unique valid class tokens in first-seen
     * order, and drops tokens that do not match a CSS class name pattern.
     *
     * @param mixed $rawValue
     * @return list<string>
     */
    private static function parseCssClassList($rawValue): array
    {
        if (!is_string($rawValue) || $rawValue === '') {
            return [];
        }

        $uniqueClassNames = [];

        foreach (explode(',', $rawValue) as $entry) {
            $entry = trim($entry);
            if ($entry === '') {
                continue;
            }

            $classTokens = preg_split('/\s+/', $entry) ?: [];
            foreach ($classTokens as $token) {
                if (preg_match('/^[A-Za-z_-][A-Za-z0-9_-]*$/', $token) === 1) {
                    $uniqueClassNames[$token] = true;
                }
            }
        }

        return array_keys($uniqueClassNames);
    }
}
