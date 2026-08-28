<?php

namespace IvyForms\Services\Template;

use IvyForms\Common\Constants\SupportedCurrencyCodes;
use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Services\Translations\BackendStrings;

/**
 * Class TemplateDefaults
 * Holds default configuration values for form templates
 *
 * @package IvyForms\Services\Template
 */
class TemplateDefaults
{
    /**
     * Cached default form styles loaded from JSON.
     *
     * @var array<string, mixed>|null
     */
    private static ?array $cachedFormStyles = null;

    /**
     * Default integration settings for new forms
     *
     * @var array<string, array<string, mixed>>
     */
    public const DEFAULT_INTEGRATION_SETTINGS = [
        'wpdatatables' => [
            'enabled' => true
        ]
    ];

    /**
     * Get default integration settings
     *
     * @return array<string, array<string, mixed>>
     */
    public static function getDefaultIntegrationSettings(): array
    {
        return self::DEFAULT_INTEGRATION_SETTINGS;
    }

    /**
     * Default payment settings for new forms.
     *
     * @var array{currency:string}
     */
    public const DEFAULT_PAYMENT_SETTINGS = [
        'currency' => SupportedCurrencyCodes::DEFAULT_CODE,
    ];

    /**
     * @return array{currency:string}
     */
    public static function getDefaultPaymentSettings(): array
    {
        return self::DEFAULT_PAYMENT_SETTINGS;
    }

    /**
     * Get default form style settings from bundled JSON (`backend/resources/defaultFormStyles.json`).
     *
     * @return array<string, mixed>
     * @throws InvalidArgumentException
     */
    public static function getDefaultFormStyles(): array
    {
        $exceptionStrings = BackendStrings::getExceptionStrings();

        if (self::$cachedFormStyles !== null) {
            return self::$cachedFormStyles;
        }

        $jsonPath = self::getDefaultFormStylesJsonPath();

        if (!is_readable($jsonPath)) {
            throw new InvalidArgumentException(
                sprintf(
                    $exceptionStrings['default_styles_json_not_found'],
                    $jsonPath
                )
            );
        }

        $jsonContent = file_get_contents($jsonPath);
        if ($jsonContent === false) {
            throw new InvalidArgumentException(
                sprintf($exceptionStrings['default_styles_json_read_failed'], $jsonPath)
            );
        }

        try {
            $decoded = json_decode($jsonContent, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $jsonException) {
            throw new InvalidArgumentException(
                sprintf($exceptionStrings['default_styles_json_parse_failed'], $jsonException->getMessage())
            );
        }

        if (!is_array($decoded)) {
            throw new InvalidArgumentException($exceptionStrings['default_styles_json_invalid_structure']);
        }

        self::$cachedFormStyles = self::filterDefaultFormStyles($decoded);

        return self::$cachedFormStyles;
    }

    /**
     * Default form styles from JSON, or a minimal safe structure if the file is missing or invalid.
     *
     * @return array<string, mixed>
     */
    public static function getDefaultFormStylesSafe(): array
    {
        try {
            return self::getDefaultFormStyles();
        } catch (InvalidArgumentException $e) {
            return self::filterDefaultFormStyles(self::getMinimalFormStyleSettingsFallback());
        }
    }

    /**
     * Minimal style settings when JSON defaults cannot be loaded (matches sanitizer-valid shape).
     *
     * @return array<string, mixed>
     */
    private static function getMinimalFormStyleSettingsFallback(): array
    {
        return [
            'stylesEnabled' => false,
            'selectedTheme' => 'ivy-default',
            'styleMode' => 'quick',
            'customCssClass' => '',
            'additionalCssClasses' => '',
            'quickMode' => [],
            'advancedMode' => [],
        ];
    }

    /**
     * Allow Pro (and other extensions) to merge plan-specific default style sections.
     *
     * @param array<string, mixed> $defaults
     * @return array<string, mixed>
     */
    private static function filterDefaultFormStyles(array $defaults): array
    {
        /** @var mixed $filtered */
        $filtered = apply_filters('ivyforms/style/filter/default_form_styles', $defaults);

        if (!is_array($filtered)) {
            error_log(
                'IvyForms: ivyforms/style/filter/default_form_styles filter must return an array; '
                . 'using bundled defaults.'
            );

            return $defaults;
        }

        return $filtered;
    }

    /**
     * Absolute path to the bundled default form styles JSON.
     *
     * @return string
     */
    private static function getDefaultFormStylesJsonPath(): string
    {
        $root = defined('IVYFORMS_PATH') ? IVYFORMS_PATH : dirname(__DIR__, 4);

        return $root . '/backend/resources/defaultFormStyles.json';
    }
}
