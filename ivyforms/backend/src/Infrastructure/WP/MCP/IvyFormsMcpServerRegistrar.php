<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

namespace IvyForms\Infrastructure\WP\MCP;

/**
 * Registers the IvyForms MCP server via the WordPress MCP adapter (Amelia-style route: mcp / ivyforms-mcp-server).
 *
 * Must use {@see IvyFormsMcpHttpTransport}: registers during the adapter's {@see 'rest_api_init'}
 * priority-15 pass and keeps the stock deferred callback so routes survive REST-server recreation.
 *
 * REST endpoint: /wp-json/mcp/ivyforms-mcp-server
 */
class IvyFormsMcpServerRegistrar
{
    /** Abilities exposed as MCP tools on this server */
    private const ABILITY_IDS = [
        // Forms
        'ivyforms/get-forms',
        'ivyforms/get-form',
        'ivyforms/create-form',
        'ivyforms/duplicate-form',
        'ivyforms/update-form-settings',
        'ivyforms/setup-multipage-form',
        'ivyforms/add-form-page',
        'ivyforms/update-form-starred',
        'ivyforms/update-form-status',
        'ivyforms/add-form-field',
        'ivyforms/add-form-fields',
        'ivyforms/duplicate-form-field',
        'ivyforms/reorder-form-fields',
        // Entries
        'ivyforms/list-entries',
        'ivyforms/get-entry',
        'ivyforms/get-entry-count',
        'ivyforms/update-entry-starred',
        // Notifications
        'ivyforms/list-notifications',
        'ivyforms/get-notification',
        'ivyforms/create-notification',
        'ivyforms/update-notification',
        'ivyforms/update-notification-settings',
        'ivyforms/duplicate-notification',
        'ivyforms/search-notifications',
        // Settings
        'ivyforms/get-settings',
        'ivyforms/get-setting',

        // Templates
        'ivyforms/list-templates',
        'ivyforms/get-template',

        // Integrations & Changelog
        'ivyforms/list-integrations',
        'ivyforms/get-integration',
        'ivyforms/get-changelog',

        // Navigation
        'ivyforms/open-form-builder',
        'ivyforms/open-entry',

        // Permission management (admin only)
        'ivyforms/get-access-rules',
        'ivyforms/list-permission-keys',
        'ivyforms/grant-role-permission',
        'ivyforms/revoke-role-permission',
        'ivyforms/grant-user-permission',
        'ivyforms/revoke-user-permission',
    ];

    /**
     * Register the IvyForms MCP server via the adapter.
     *
     * @param mixed $adapter The adapter instance (\WP\MCP\Core\McpAdapter).
     */
    public static function init($adapter): void
    {
        if (!McpFeatureGate::isEnabled()) {
            return;
        }

        $result = $adapter->create_server(
            'ivyforms-mcp-server',
            'mcp',
            'ivyforms-mcp-server',
            'IvyForms MCP Server',
            'MCP server for the IvyForms Contact Form Builder plugin. ' .
            'Manage forms, entries, notifications, settings and templates.',
            '1.0.0',
            array(IvyFormsMcpHttpTransport::class),
            null,
            null,
            self::ABILITY_IDS,
            array(),
            array(),
            static function () {
                if (is_user_logged_in()) {
                    return McpAbilityPermissions::canUseMcp();
                }

                // Do not sanitize credentials: application passwords are exact-match values.
                $username = (string) (filter_input(INPUT_SERVER, 'PHP_AUTH_USER', FILTER_UNSAFE_RAW) ?? '');
                $password = (string) (filter_input(INPUT_SERVER, 'PHP_AUTH_PW', FILTER_UNSAFE_RAW) ?? '');
                $username = $username !== '' ? wp_unslash($username) : '';
                $password = $password !== '' ? wp_unslash($password) : '';

                if (!$username || !$password) {
                    return false;
                }

                $user = wp_authenticate_application_password(null, $username, $password);

                if ($user instanceof \WP_User) {
                    // restRequest() and ability callbacks rely on the current user; set it once
                    // here so the rest of the pipeline (capability checks, nonces) sees the
                    // authenticated identity instead of a logged-out anonymous user.
                    wp_set_current_user($user->ID);

                    return McpAbilityPermissions::canUseMcp();
                }

                return false;
            }
        );

        if ($result instanceof \WP_Error) {
            error_log('IvyForms MCP: create_server failed – ' . $result->get_error_message());
        }
    }
}
