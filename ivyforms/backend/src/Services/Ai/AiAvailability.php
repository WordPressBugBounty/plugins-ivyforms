<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace IvyForms\Services\Ai;

/**
 * Reports whether WordPress AI Client can generate forms for IvyForms.
 */
class AiAvailability
{
    public const STATUS_AVAILABLE = 'available';
    public const STATUS_WORDPRESS_UPDATE_REQUIRED = 'wordpress_update_required';
    public const STATUS_AI_DISABLED = 'ai_disabled';
    public const STATUS_AI_CLIENT_MISSING = 'ai_client_missing';
    public const STATUS_TEXT_GENERATION_UNAVAILABLE = 'text_generation_unavailable';

    private const AVAILABILITY_CACHE_TRANSIENT = 'ivyforms_ai_text_generation_available';

    /** Short TTL — capability checks may inspect remote provider metadata. */
    private const AVAILABILITY_CACHE_TTL = 60;

    /** @var bool|null Request-scoped memo of the text-generation capability check. */
    private static $textGenerationMemo = null;

    public static function isSupported(): bool
    {
        return function_exists('wp_supports_ai')
            && wp_supports_ai()
            && function_exists('wp_ai_client_prompt');
    }

    /**
     * Cached text-generation capability check for admin/UI.
     *
     * Never probes providers: missing/expired cache is treated as unavailable.
     * Call refreshTextGenerationSupport() outside render paths to warm the cache.
     */
    public static function hasTextGenerationSupport(): bool
    {
        if (!self::isSupported()) {
            return false;
        }

        if (self::$textGenerationMemo !== null) {
            return self::$textGenerationMemo;
        }

        $cached = get_transient(self::AVAILABILITY_CACHE_TRANSIENT);
        if ($cached === '1' || $cached === '0') {
            self::$textGenerationMemo = $cached === '1';

            return self::$textGenerationMemo;
        }

        return false;
    }

    /**
     * Fresh capability check for generate requests and non-render warm paths.
     */
    public static function refreshTextGenerationSupport(): bool
    {
        if (!self::isSupported()) {
            return false;
        }

        $supported = self::detectTextGenerationSupport();
        set_transient(
            self::AVAILABILITY_CACHE_TRANSIENT,
            $supported ? '1' : '0',
            self::AVAILABILITY_CACHE_TTL
        );
        self::$textGenerationMemo = $supported;

        return $supported;
    }

    /**
     * Warm the capability cache when missing/expired, without forcing a probe every request.
     */
    public static function maybeRefreshTextGenerationSupport(): void
    {
        if (!self::isSupported()) {
            return;
        }

        $cached = get_transient(self::AVAILABILITY_CACHE_TRANSIENT);
        if ($cached === '1' || $cached === '0') {
            return;
        }

        self::refreshTextGenerationSupport();
    }

    /**
     * True when AI can be used end-to-end for form generation.
     */
    public static function isAvailable(): bool
    {
        return self::getStatus() === self::STATUS_AVAILABLE;
    }

    /**
     * Resolve the AI status using the cached text-generation capability.
     */
    public static function getStatus(): string
    {
        $preliminaryStatus = self::getPreliminaryStatus();
        if ($preliminaryStatus !== null) {
            return $preliminaryStatus;
        }

        return self::hasTextGenerationSupport()
            ? self::STATUS_AVAILABLE
            : self::STATUS_TEXT_GENERATION_UNAVAILABLE;
    }

    /**
     * Resolve the AI status, forcing a fresh text-generation capability probe.
     */
    public static function getRefreshedStatus(): string
    {
        $preliminaryStatus = self::getPreliminaryStatus();
        if ($preliminaryStatus !== null) {
            return $preliminaryStatus;
        }

        return self::refreshTextGenerationSupport()
            ? self::STATUS_AVAILABLE
            : self::STATUS_TEXT_GENERATION_UNAVAILABLE;
    }

    /**
     * Environment-level status that does not depend on the text-generation probe.
     *
     * @return string|null A terminal status, or null when text-generation capability must be checked.
     */
    private static function getPreliminaryStatus(): ?string
    {
        if (!function_exists('wp_supports_ai')) {
            return self::STATUS_WORDPRESS_UPDATE_REQUIRED;
        }

        if (!wp_supports_ai()) {
            return self::STATUS_AI_DISABLED;
        }

        if (!function_exists('wp_ai_client_prompt')) {
            return self::STATUS_AI_CLIENT_MISSING;
        }

        return null;
    }

    /**
     * Admin URL for WordPress Connectors / AI provider settings.
     */
    public static function getConnectorsSettingsUrl(): string
    {
        return admin_url('options-connectors.php');
    }

    public static function getWordPressUpdateUrl(): string
    {
        return current_user_can('update_core') ? self_admin_url('update-core.php') : '';
    }

    public static function getAiPluginInstallUrl(): string
    {
        if (!current_user_can('install_plugins')) {
            return '';
        }

        return self_admin_url('plugin-install.php?s=AI&tab=search&type=term');
    }

    /**
     * Whether AI may generate multi-page forms (Pro gate; default false).
     */
    public static function canUseMultipage(): bool
    {
        /**
         * Gate AI multipage generation. Lite default is false; Pro enables when licensed.
         *
         * @since 0.1.0
         *
         * @param bool $allowed
         * @return bool
         */
        return (bool) apply_filters('ivyforms/ai/can_use_multipage', false);
    }

    private static function detectTextGenerationSupport(): bool
    {
        try {
            return wp_ai_client_prompt('IvyForms capability check')
                ->is_supported_for_text_generation();
        } catch (\Throwable $exception) {
            return false;
        }
    }
}
