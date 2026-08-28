<?php

namespace IvyForms\Services\Shortcode\Helpers;

use IvyForms\Factory\Field\FieldFactory;
use IvyForms\Factory\Security\SecurityServiceFactory;
use IvyForms\Services\Settings\SettingsService;
use IvyForms\Services\Security\SecurityService;
use IvyForms\Services\Translations\FrontendStrings;
use IvyForms\Routes\Routes;
use IvyForms\Services\Shortcode\InlineStyleHelper;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Helper utilities used by shortcode-related services.
 * Consolidates asset registration and small helpers so that ShortcodeService
 * can stay focused on rendering and orchestration.
 */
class ShortcodeHelper
{
    /**
     * Register frontend style handles early so external integrations can safely depend on them.
     *
     * @return void
     */
    public static function registerFrontendStyleHandles(): void
    {
        [, , $styleSrc, $stylePublicSrc, $scriptVersion] = self::resolveAssetPaths();

        if (!wp_style_is('ivyforms_style_index', 'registered')) {
            wp_register_style('ivyforms_style_index', $styleSrc ?: false, [], $scriptVersion);
        }

        if (!wp_style_is('ivyforms_style_public', 'registered')) {
            wp_register_style('ivyforms_style_public', $stylePublicSrc ?: false, [], $scriptVersion);
        }
    }

    /**
     * Build SecurityService instance
     *
     * @return SecurityService
     */
    public static function getSecurityService(): SecurityService
    {
        $settingsService = new SettingsService();
        $securityFactory = new SecurityServiceFactory($settingsService);
        return new SecurityService($securityFactory);
    }

    /**
     * Convert form list to runtime Field objects expected by the security service
     *
     * @param array<string, array<string, mixed>> $formList
     * @return array<object>
     */
    public static function getAllFormFields(array $formList): array
    {
        $allFields = [];

        foreach ($formList as $formData) {
            foreach ($formData['fields'] as $fieldData) {
                $field = FieldFactory::create($fieldData);
                $allFields[] = $field;
            }
        }

        return $allFields;
    }

    /**
     * Enqueue provider script if configured
     *
     * @param string $handle
     * @param array<string, mixed> $providerConfig
     * @return void
     */
    public static function enqueueProviderScript(string $handle, array $providerConfig): void
    {
        $scriptUrl = $providerConfig['scriptUrl'] ?? '';
        if (empty($scriptUrl)) {
            return;
        }

        wp_enqueue_script(
            $handle,
            $scriptUrl,
            [],
            null,
            true
        );
    }

    /**
     * Enqueue Google reCAPTCHA script if present in captcha configuration
     *
     * @param array<string, mixed> $captchaConfig Top-level captcha configuration array (may contain 'recaptcha')
     * @return void
     */
    public static function enqueueGoogleRecaptchaScript(array $captchaConfig): void
    {
        if (!isset($captchaConfig['recaptcha'])) {
            return;
        }

        self::enqueueProviderScript('google-recaptcha', $captchaConfig['recaptcha']);
    }

    /**
     * Enqueue Cloudflare Turnstile script if present in captcha configuration
     *
     * @param array<string, mixed> $captchaConfig Top-level captcha configuration array (may contain 'turnstile')
     * @return void
     */
    public static function enqueueTurnstileScript(array $captchaConfig): void
    {
        if (!isset($captchaConfig['turnstile'])) {
            return;
        }

        self::enqueueProviderScript('cloudflare-turnstile', $captchaConfig['turnstile']);
    }

    /**
     * Enqueue hCaptcha script if present in captcha configuration
     *
     * @param array<string, mixed> $captchaConfig Top-level captcha configuration array (may contain 'hcaptcha')
     * @return void
     */
    public static function enqueueHCaptchaScript(array $captchaConfig): void
    {
        if (!isset($captchaConfig['hcaptcha'])) {
            return;
        }

        self::enqueueProviderScript('hcaptcha', $captchaConfig['hcaptcha']);
    }

    /**
     * Register frontend assets (script, styles) and localize data for IvyForms shortcode.
     * Intended to be called from ShortcodeService::enqueueScripts()
     *
     * @param array<string, array<string, mixed>> $formList
     * @return void
     */
    public static function registerFrontendAssets(array $formList): void
    {
        // Resolve paths and handles
        [$scriptId, $scriptSrc, $styleSrc, $stylePublicSrc, $scriptVersion] = self::resolveAssetPaths();

        // Ensure style handles are available for dependencies before enqueuing.
        self::registerFrontendStyleHandles();

        // Enqueue main script and styles
        self::enqueueMainScript($scriptId, $scriptSrc, $scriptVersion);

        if ($styleSrc) { // @phpstan-ignore-line
            self::enqueueStyles($styleSrc, $stylePublicSrc, $scriptVersion);
        }

        // Enqueue captcha scripts and localize captcha config
        self::enqueueCaptchaAndLocalize($scriptId, $formList);

        // Localize common frontend data (forms, labels, urls, api settings, date formats)
        self::localizeFormListAndLabels($scriptId, $formList);

        do_action('ivyforms/shortcode/enqueue_scripts', $scriptId);
    }

    /**
     * Resolve script/style handles and URLs
     *
     * @return array{0:string,1:string,2:string,3:string,4:mixed}
     */
    private static function resolveAssetPaths(): array
    {
        $scriptId = IVYFORMS_DEV ? 'ivyforms_scripts_dev_vite' : 'ivyforms_script_index';   // @phpstan-ignore-line
        $scriptSrc = IVYFORMS_DEV ? // @phpstan-ignore-line
            'http://localhost:5173/src/assets/js/public/public.ts' :
            IVYFORMS_URL . 'frontend/dist/public.js';

        $styleSrc = IVYFORMS_DEV ? '' : IVYFORMS_URL . 'frontend/dist/index.css'; // @phpstan-ignore-line
        $stylePublicSrc = IVYFORMS_DEV ? '' : IVYFORMS_URL . 'frontend/dist/public.css'; // @phpstan-ignore-line
        $scriptVersion = IVYFORMS_DEV ? null : IVYFORMS_VERSION;// @phpstan-ignore-line

        return [$scriptId, $scriptSrc, $styleSrc, $stylePublicSrc, $scriptVersion];
    }

    /**
     * Enqueue the main frontend script
     *
     * @param string $scriptId
     * @param string $scriptSrc
     * @param mixed $scriptVersion
     * @return void
     */
    private static function enqueueMainScript(string $scriptId, string $scriptSrc, $scriptVersion): void
    {
        wp_enqueue_script(
            $scriptId,
            $scriptSrc,
            [],
            $scriptVersion,
            true
        );
    }

    /**
     * Enqueue styles and inline css
     *
     * @param string $styleSrc
     * @param string $stylePublicSrc
     * @param mixed $scriptVersion
     * @return void
     */
    private static function enqueueStyles(string $styleSrc, string $stylePublicSrc, $scriptVersion): void
    {
        wp_enqueue_style('ivyforms_style_index', $styleSrc, [], $scriptVersion);
        wp_enqueue_style('ivyforms_style_public', $stylePublicSrc, [], $scriptVersion);

        // Use helper for inline styles
        wp_add_inline_style(
            'ivyforms_style_index',
            InlineStyleHelper::getFrontendInlineCss()
        );
    }

    /**
     * Enqueue captcha scripts and localize captcha configs for consumers
     *
     * @param string $scriptId
     * @param array<string, array<string, mixed>> $formList
     * @return void
     */
    private static function enqueueCaptchaAndLocalize(string $scriptId, array $formList): void
    {
        $securityService = self::getSecurityService();
        $formFields = self::getAllFormFields($formList);

        $securityConfig = $securityService->getFrontendSecurityConfig($formFields);

        if ($securityConfig['captcha']['enabled'] ?? false) {
            if (isset($securityConfig['captcha']['recaptcha'])) {
                self::enqueueGoogleRecaptchaScript($securityConfig['captcha']);
            }
            if (isset($securityConfig['captcha']['turnstile'])) {
                self::enqueueTurnstileScript($securityConfig['captcha']);
            }
            if (isset($securityConfig['captcha']['hcaptcha'])) {
                self::enqueueHCaptchaScript($securityConfig['captcha']);
            }
        }

        wp_localize_script(
            $scriptId,
            'wpIvyRecaptchaConfig',
            $securityConfig['captcha'] ?? []
        );

        wp_localize_script(
            $scriptId,
            'wpIvyTurnstileConfig',
            $securityConfig['captcha'] ?? []
        );

        wp_localize_script(
            $scriptId,
            'wpIvyHCaptchaConfig',
            $securityConfig['captcha'] ?? []
        );
    }

    /**
     * Localize form list, labels, urls, api settings and date format
     *
     * @param string $scriptId
     * @param array<string, array<string, mixed>> $formList
     * @return void
     */
    private static function localizeFormListAndLabels(string $scriptId, array $formList): void
    {
        wp_localize_script(
            $scriptId,
            'wpIvyFormDataList',
            $formList
        );

        $frontendLabels = array_merge(
            FrontendStrings::getFormRenderStrings()
        );

        /**
         * Filter frontend labels before localizing
         * Allows Pro version to merge additional strings
         *
         * @since 0.1.0
         * @param array $frontendLabels Array of frontend label strings
         */
        $frontendLabels = apply_filters('ivyforms/shortcode/frontend_labels', $frontendLabels);

        wp_localize_script($scriptId, 'wpIvyLabels', $frontendLabels);

        wp_localize_script(
            $scriptId,
            'wpIvyUrls',
            [
                'pluginURL' => IVYFORMS_URL,
                'siteURL'   => esc_url_raw(site_url()),
            ]
        );

        wp_localize_script(
            $scriptId,
            'wpIvyApiSettings',
            [
                'root' => esc_url_raw(rest_url()),
                'nonce' => wp_create_nonce('wp_rest'),
                'namespace' => Routes::$routeNamespace,
            ]
        );

        wp_localize_script(
            $scriptId,
            'wpIvyDateFormat',
            [
                'dateFormat'     => get_option('date_format'),
                'timeFormat'     => get_option('time_format'),
                'dateTimeFormat' => get_option('date_format') . ' ' . get_option('time_format'),
                'locale'         => get_locale(),
            ]
        );
    }

    /**
     * Add script attribute for module type
     *
     * @param array<string, mixed> $attributes
     *
     * @return array<string, mixed>
     */
    public static function addScriptAttribute(array $attributes): array
    {
        $scriptArray = [
            'ivyforms_scripts_dev_main-js',
            'ivyforms_scripts_dev_vite-js',
            'ivyforms_script_index-js',
            'ivyforms_scripts_public-js',
        ];

        if (isset($attributes['id']) && in_array($attributes['id'], $scriptArray, true)) {
            $attributes['type'] = 'module';
        }

        return $attributes;
    }
}
