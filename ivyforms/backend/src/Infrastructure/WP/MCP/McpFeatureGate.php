<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

namespace IvyForms\Infrastructure\WP\MCP;

use IvyForms\Services\Settings\SettingsService;

/**
 * Central feature gate for IvyForms MCP.
 *
 * MCP exposes IvyForms capabilities to AI clients. It is ON by default; site owners can
 * turn it off via IvyForms Settings → General ("Enable MCP for AI assistants") or force a
 * value with the {@code ivyforms/mcp/enabled} filter. Access is always scoped to each
 * user's IvyForms permissions (see {@see McpAbilityPermissions}).
 *
 * Hard prerequisites (always enforced):
 * - The Abilities API must be loaded ({@see wp_register_ability}).
 * - WordPress core must be at least {@see IVYFORMS_MCP_MIN_WP_VERSION}.
 */
class McpFeatureGate
{
    public const FILTER_ENABLED = 'ivyforms/mcp/enabled';

    /**
     * Whether MCP should bootstrap and register routes/abilities.
     */
    public static function isEnabled(): bool
    {
        if (!function_exists('wp_register_ability')) {
            return false;
        }

        if (!self::wordPressVersionSatisfiesMinimum()) {
            return false;
        }

        return (bool) apply_filters(self::FILTER_ENABLED, self::isEnabledInStoredSettings());
    }

    /**
     * Whether MCP is enabled via IvyForms Settings → General (ivyforms_settings option).
     *
     * Enabled by default: when no explicit choice has been stored we treat MCP as ON, so
     * only an explicit opt-out (mcp_enabled = false) disables it.
     */
    private static function isEnabledInStoredSettings(): bool
    {
        if (!function_exists('get_option')) {
            return true;
        }

        $stored = (new SettingsService())->getSetting('general', 'mcp_enabled');

        return self::coerceStoredEnabled($stored);
    }

    /**
     * Coerce a stored mcp_enabled value to a boolean, defaulting to enabled.
     *
     * @param mixed $rawEnabled
     */
    public static function coerceStoredEnabled($rawEnabled): bool
    {
        if (is_bool($rawEnabled)) {
            return $rawEnabled;
        }

        if (is_string($rawEnabled) && trim($rawEnabled) === '') {
            return true;
        }

        if (is_string($rawEnabled) || is_int($rawEnabled)) {
            return filter_var($rawEnabled, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true;
        }

        return true;
    }

    /**
     * True when the running WordPress version is at least the documented minimum.
     */
    private static function wordPressVersionSatisfiesMinimum(): bool
    {
        $minimum = defined('IVYFORMS_MCP_MIN_WP_VERSION') ? (string) IVYFORMS_MCP_MIN_WP_VERSION : '6.9';

        return version_compare((string) get_bloginfo('version'), $minimum, '>=');
    }
}
