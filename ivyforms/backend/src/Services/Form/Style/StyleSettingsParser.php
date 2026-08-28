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
 * Class StyleSettingsParser
 *
 * Parses style settings and extracts active colors and styles for CSS generation.
 * `styleMode` (`quick` | `advanced`) is a builder UI concern only; output must match
 * the same visual rules regardless of tab. `quickMode` supplies the shortcut subset,
 * while `advancedMode` holds the full tree (kept in sync in the app). This parser
 * always applies `quickMode` first, then `advancedMode` (plus general/button slices)
 * so the rendered form stays consistent.
 *
 * @package IvyForms\Services\Form\StyleHelpers
 */
class StyleSettingsParser
{
    /**
     * @var string[]
     */
    private const ALLOWED_ALIGNMENTS = ['left', 'center', 'right'];

    /**
     * @var array<string, mixed>
     */
    private array $styleSettings;

    /**
     * @var string
     */
    private string $styleMode;

    /**
     * @var array<string, string>
     */
    private array $activeColors = [];

    /**
     * @var array<string, string>
     */
    private array $activeStyles = [];

    /**
     * Constructor
     *
     * @param array<string, mixed> $styleSettings
     */
    public function __construct(array $styleSettings)
    {
        $this->styleSettings = $styleSettings;
        $this->styleMode = $styleSettings['styleMode'] ?? 'quick';
        $this->parse();
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
     * Get style mode
     *
     * @return string
     */
    public function getStyleMode(): string
    {
        return $this->styleMode;
    }

    /**
     * Parse style settings
     *
     * @return void
     */
    private function parse(): void
    {
        $this->activeColors = $this->styleSettings['colors'] ?? [];

        $this->parseQuickModeSettings();
        $this->parseGeneralSpacingSettings();
        $this->parseAdvancedButtonSettings();
        $this->parseAdvancedModeSettings();
    }

    /**
     * Parse quick mode settings
     *
     * @return void
     */
    private function parseQuickModeSettings(): void
    {
        if (empty($this->styleSettings['quickMode'])) {
            return;
        }

        $quick = $this->styleSettings['quickMode'];

        $this->setIfNotEmpty($this->activeColors, 'formBackground', $quick['formBackground'] ?? null);
        $this->setIfNotEmpty($this->activeColors, 'formBorderColor', $quick['formBorderColor'] ?? null);
        $this->setIfNotEmpty($this->activeStyles, 'formBorderWidth', $quick['formBorderWidth'] ?? null);

        $this->resolveCornerSetting('formCornerRadius', $quick['formCorner'] ?? null);

        $this->setIfNotEmpty($this->activeColors, 'formTitleColor', $quick['formTitleText'] ?? null);
        $this->setIfNotEmpty($this->activeColors, 'fieldBackground', $quick['fieldBackground'] ?? null);
        $this->setIfNotEmpty($this->activeColors, 'fieldText', $quick['fieldText'] ?? null);
        $this->setIfNotEmpty($this->activeColors, 'fieldDescriptionColor', $quick['fieldText'] ?? null);
        $this->setIfNotEmpty($this->activeColors, 'fieldBorderColor', $quick['fieldBorderColor'] ?? null);
        $this->activeStyles['formBorderStyle'] = 'solid';
        $this->activeStyles['fieldBorderStyle'] = 'solid';

        $this->resolveCornerSetting('fieldCornerRadius', $quick['fieldCorner'] ?? null);

        $this->setIfNotEmpty($this->activeStyles, 'fieldBorderWidth', $quick['fieldBorderWidth'] ?? null);
        $this->setIfNotEmpty($this->activeStyles, 'fieldPaddingLeftRight', $quick['fieldPaddingLeftRight'] ?? null);
        $this->setIfNotEmpty($this->activeStyles, 'fieldPaddingTopBottom', $quick['fieldPaddingTopBottom'] ?? null);
        $this->setIfNotEmpty($this->activeColors, 'fieldLabels', $quick['labelFontColor'] ?? null);

        $this->setPlaceholderColor($quick);

        $this->setIfNotEmpty($this->activeColors, 'buttonBackground', $quick['buttonBackground'] ?? null);
        $this->setIfNotEmpty($this->activeColors, 'buttonBorderColor', $quick['buttonBorderColor'] ?? null);
        $this->setIfNotEmpty($this->activeStyles, 'buttonBorderWidth', $quick['buttonBorderWidth'] ?? null);
        $this->setIfNotEmpty($this->activeStyles, 'buttonPaddingLeftRight', $quick['buttonPaddingLeftRight'] ?? null);
        $this->setIfNotEmpty($this->activeStyles, 'buttonPaddingTopBottom', $quick['buttonPaddingTopBottom'] ?? null);
        $this->setIfNotEmpty($this->activeStyles, 'buttonWidth', $quick['buttonWidth'] ?? null);
        $this->setIfNotEmpty($this->activeStyles, 'buttonHeight', $quick['buttonHeight'] ?? null);
        $this->setIfNotEmpty($this->activeColors, 'buttonText', $quick['buttonText'] ?? null);

        $this->setCheckboxRadioColors($quick);

        $this->setIfNotEmpty($this->activeStyles, 'verticalSpacing', $quick['verticalSpacing'] ?? null);
    }

    /**
     * Parse general spacing settings (form padding and vertical spacing)
     *
     * @return void
     */
    private function parseGeneralSpacingSettings(): void
    {
        if (empty($this->styleSettings['advancedMode']['general'])) {
            return;
        }

        $general = $this->styleSettings['advancedMode']['general'];

        $this->setIfNotEmpty($this->activeStyles, 'formPaddingLeftRight', $general['formPaddingLeftRight'] ?? null);
        $this->setIfNotEmpty($this->activeStyles, 'formPaddingTopBottom', $general['formPaddingTopBottom'] ?? null);

        if (empty($this->activeStyles['verticalSpacing'])) {
            $this->setIfNotEmpty($this->activeStyles, 'verticalSpacing', $general['verticalSpacing'] ?? null);
        }

        $this->setIfNotEmpty($this->activeStyles, 'formBorderStyle', $general['formBorderStyle'] ?? null);
    }

    /**
     * Parse advanced button settings (padding, width, height)
     *
     * @return void
     */
    private function parseAdvancedButtonSettings(): void
    {
        if (empty($this->styleSettings['advancedMode']['buttonStates']['normal'])) {
            return;
        }

        $buttonNormal = $this->styleSettings['advancedMode']['buttonStates']['normal'];

        // Advanced mode settings override quick mode
        if (!empty($buttonNormal['background'])) {
            $this->activeColors['buttonBackground'] = $buttonNormal['background'];
        }
        if (!empty($buttonNormal['borderColor'])) {
            $this->activeColors['buttonBorderColor'] = $buttonNormal['borderColor'];
        }
        if (!empty($buttonNormal['borderWidth'])) {
            $this->activeStyles['buttonBorderWidth'] = $buttonNormal['borderWidth'];
        }
        if (!empty($buttonNormal['textColor'])) {
            $this->activeColors['buttonText'] = $buttonNormal['textColor'];
        }

        $this->resolveCornerSetting('buttonCornerRadius', $buttonNormal['corner'] ?? null);

        // Button padding
        $this->setIfNotEmpty($this->activeStyles, 'buttonPaddingLeftRight', $buttonNormal['paddingLeftRight'] ?? null);
        $this->setIfNotEmpty($this->activeStyles, 'buttonPaddingTopBottom', $buttonNormal['paddingTopBottom'] ?? null);

        // Button dimensions
        $this->setIfNotEmpty($this->activeStyles, 'buttonWidth', $buttonNormal['width'] ?? null);
        $this->setIfNotEmpty($this->activeStyles, 'buttonHeight', $buttonNormal['height'] ?? null);
    }

    /**
     * Parse the full advancedMode tree into active colors/styles
     *
     * @return void
     */
    private function parseAdvancedModeSettings(): void
    {
        if (empty($this->styleSettings['advancedMode'])) {
            return;
        }

        $adv = $this->styleSettings['advancedMode'];

        $this->setIfNotEmpty(
            $this->activeColors,
            'fieldLabels',
            $adv['fieldLabels']['label']['fontColor'] ?? null
        );
        $this->setIfNotEmpty(
            $this->activeStyles,
            'fieldLabelPaddingLeftRight',
            $adv['fieldLabels']['label']['paddingLeftRight'] ?? null
        );
        $this->setIfNotEmpty(
            $this->activeStyles,
            'fieldLabelPaddingTopBottom',
            $adv['fieldLabels']['label']['paddingTopBottom'] ?? null
        );
        $this->setIfNotEmpty(
            $this->activeStyles,
            'fieldLabelAlignment',
            $this->sanitizeAlignment($adv['fieldLabels']['label']['alignment'] ?? null)
        );

        $this->setIfNotEmpty(
            $this->activeColors,
            'fieldPlaceholder',
            $adv['fieldPlaceholder']['fontColor'] ?? null
        );
        $this->setIfNotEmpty(
            $this->activeStyles,
            'fieldPlaceholderPaddingLeftRight',
            $adv['fieldPlaceholder']['paddingLeftRight'] ?? null
        );
        $this->setIfNotEmpty(
            $this->activeStyles,
            'fieldPlaceholderAlignment',
            $this->sanitizeAlignment($adv['fieldPlaceholder']['alignment'] ?? null)
        );

        $this->setIfNotEmpty(
            $this->activeColors,
            'formTitleColor',
            $adv['formTitle']['fontColor'] ?? null
        );
        $this->setIfNotEmpty(
            $this->activeStyles,
            'formTitlePaddingLeftRight',
            $adv['formTitle']['paddingLeftRight'] ?? null
        );
        $this->setIfNotEmpty(
            $this->activeStyles,
            'formTitlePaddingTopBottom',
            $adv['formTitle']['paddingTopBottom'] ?? null
        );

        $this->setIfNotEmpty(
            $this->activeColors,
            'formDescriptionColor',
            $adv['formDescription']['fontColor'] ?? null
        );
        $this->setIfNotEmpty(
            $this->activeStyles,
            'formDescriptionPaddingLeftRight',
            $adv['formDescription']['paddingLeftRight'] ?? null
        );
        $this->setIfNotEmpty(
            $this->activeStyles,
            'formDescriptionPaddingTopBottom',
            $adv['formDescription']['paddingTopBottom'] ?? null
        );
        $this->setIfNotEmpty(
            $this->activeColors,
            'requiredIndicator',
            $adv['fieldLabels']['requiredIndicator']['fontColor'] ?? null
        );

        $this->setIfNotEmpty(
            $this->activeColors,
            'fieldDescriptionColor',
            $adv['fieldStates']['default']['descriptionColor'] ?? null
        );

        $this->setIfNotEmpty(
            $this->activeStyles,
            'fieldBorderStyle',
            $adv['fieldStates']['default']['borderStyle'] ?? null
        );

        $this->setIfNotEmpty(
            $this->activeStyles,
            'confirmationBorderStyle',
            $adv['confirmationMessage']['borderStyle'] ?? null
        );

        // Rating
        $rating = is_array($adv['rating'] ?? null) ? $adv['rating'] : [];
        $this->setIfNotEmpty(
            $this->activeStyles,
            'ratingIconSize',
            $rating['iconSize'] ?? null
        );
        $this->setIfNotEmpty(
            $this->activeColors,
            'ratingIconColor',
            $rating['iconColor'] ?? null
        );

        // Date & Time Picker
        $dateTimePicker = is_array($adv['dateTimePicker'] ?? null) ? $adv['dateTimePicker'] : [];
        $this->setIfNotEmpty(
            $this->activeColors,
            'dateTimeCalendarBackground',
            $dateTimePicker['calendarBackground'] ?? null
        );
        $this->setIfNotEmpty(
            $this->activeColors,
            'dateTimeNumberTextColor',
            $dateTimePicker['numberTextColor'] ?? null
        );
        $this->setIfNotEmpty(
            $this->activeColors,
            'dateTimeSelectionColor',
            $dateTimePicker['selectionColor'] ?? null
        );

        // Select
        $select = is_array($adv['select'] ?? null) ? $adv['select'] : [];
        $this->setIfNotEmpty(
            $this->activeColors,
            'selectTagColor',
            $select['tagColor'] ?? null
        );
        $this->setIfNotEmpty(
            $this->activeColors,
            'selectTagTextColor',
            $select['tagTextColor'] ?? null
        );
        $this->setIfNotEmpty(
            $this->activeColors,
            'selectOptionHoverColor',
            $select['optionHoverColor'] ?? null
        );
        $this->setIfNotEmpty(
            $this->activeColors,
            'selectSelectionTextColor',
            $select['selectionTextColor'] ?? null
        );
        $this->setIfNotEmpty(
            $this->activeColors,
            'selectSelectionBackgroundColor',
            $select['selectionBackgroundColor'] ?? null
        );
    }

    /**
     * Set a scalar string on activeColors/activeStyles when the value is meaningful.
     *
     * Allows CSS zero tokens such as "0", "0px", and numeric 0. Arrays are ignored
     * (structured values like corners use {@see resolveCornerSetting} instead).
     *
     * @param array<string, string> $target
     * @param string $key
     * @param mixed $value
     * @return void
     */
    private function setIfNotEmpty(array &$target, string $key, $value): void
    {
        if (null === $value || false === $value) {
            return;
        }

        if (is_array($value)) {
            return;
        }

        if (is_string($value)) {
            if ('' === $value) {
                return;
            }
            $target[$key] = $value;
            return;
        }

        if (is_numeric($value)) {
            $target[$key] = (string) $value;
        }
    }

    /**
     * Sanitize alignment value.
     *
     * @param mixed $value
     * @return string|null
     */
    private function sanitizeAlignment($value): ?string
    {
        if (!is_string($value) || '' === $value) {
            return null;
        }

        return in_array($value, self::ALLOWED_ALIGNMENTS, true) ? $value : null;
    }

    /**
     * Resolve corner setting and store in activeStyles
     *
     * @param string $key
     * @param mixed $cornerSetting
     * @return void
     */
    private function resolveCornerSetting(string $key, $cornerSetting): void
    {
        if (empty($cornerSetting)) {
            return;
        }

        $resolved = CornerRadiusResolver::resolve($cornerSetting);
        if (!empty($resolved)) {
            $this->activeStyles[$key] = $resolved;
        }
    }

    /**
     * Set placeholder color with fallback to field text
     *
     * @param array<string, mixed> $quick
     * @return void
     */
    private function setPlaceholderColor(array $quick): void
    {
        if (!empty($quick['placeholderFontColor'])) {
            $this->activeColors['fieldPlaceholder'] = $quick['placeholderFontColor'];
            return;
        }

        if (!empty($quick['fieldText'])) {
            $this->activeColors['fieldPlaceholder'] = $quick['fieldText'];
        }
    }

    /**
     * Set checkbox and radio colors
     *
     * @param array<string, mixed> $quick
     * @return void
     */
    private function setCheckboxRadioColors(array $quick): void
    {
        if (empty($quick['checkboxRadioSelection'])) {
            return;
        }

        $this->activeColors['checkboxRadioSelection'] = $quick['checkboxRadioSelection'];
        $this->activeColors['checkboxRadioBorder'] = $quick['checkboxRadioBorderColor']
            ?? $quick['checkboxRadioSelection'];

        if (!empty($quick['checkboxRadioFontColor'])) {
            $this->activeColors['checkboxRadioLabel'] = $quick['checkboxRadioFontColor'];
        }
    }
}
