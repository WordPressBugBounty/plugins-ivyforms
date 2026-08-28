<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 */

namespace IvyForms\Services\Permissions;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Site-wide access helpers for IvyForms admin (REST, menus, previews).
 *
 * Implicit elevated access (no IvyForms caps required):
 * - Multisite: super admins only.
 * - Single site: users who can manage_options (typically Administrators).
 */
final class IvyFormsAccess
{
    public const CAP_MANAGE_ALL = 'ivyforms_manage_all';

    public const CAP_ACCESS_PLUGIN = 'ivyforms_access_plugin';

    public static function hasImplicitElevatedAccess(): bool
    {
        if (!function_exists('wp_get_current_user')) {
            return false;
        }

        if (function_exists('is_multisite') && is_multisite()) {
            return function_exists('is_super_admin') && is_super_admin();
        }

        return current_user_can('manage_options');
    }

    public static function userHasImplicitElevatedAccess(\WP_User $user): bool
    {
        if (function_exists('is_multisite') && is_multisite()) {
            return function_exists('is_super_admin') && is_super_admin((int) $user->ID);
        }

        return user_can($user, 'manage_options');
    }

    /**
     * Users who already have site-wide / full IvyForms admin access (not worth listing for user-targeted rules).
     *
     * Do not exclude based on ivyforms_access_plugin: that cap is granted to delegated roles (e.g. Editor)
     * and would hide every member of those roles from the picker.
     */
    public static function userShouldBeExcludedFromPermissionsPicker(\WP_User $user): bool
    {
        if (self::userHasImplicitElevatedAccess($user)) {
            return true;
        }

        return user_can($user, self::CAP_MANAGE_ALL);
    }

    public static function canAccessPluginAdminRoutes(): bool
    {
        return self::hasImplicitElevatedAccess()
            || current_user_can(self::CAP_MANAGE_ALL)
            || current_user_can(PermissionCatalog::wpCapability('access_settings_page'))
            || current_user_can(self::CAP_ACCESS_PLUGIN);
    }

    public static function canAccessFormPreview(): bool
    {
        return self::hasImplicitElevatedAccess()
            || current_user_can(self::CAP_MANAGE_ALL)
            || current_user_can(PermissionCatalog::wpCapability('add_edit_forms'))
            || current_user_can(self::CAP_ACCESS_PLUGIN);
    }

    /**
     * Whether the current user may access IvyForms permissions management (settings tab and REST).
     *
     * Matches WordPress site administrators: manage_options on single site, super admins on
     * multisite, or the administrator role. Delegated users with only access_settings_page
     * are excluded.
     */
    public static function currentUserCanAccessPermissionsSettings(): bool
    {
        if (self::hasImplicitElevatedAccess()) {
            return true;
        }

        if (!function_exists('wp_get_current_user')) {
            return false;
        }

        return self::userHasAdministratorRole(wp_get_current_user());
    }

    public static function userHasAdministratorRole(\WP_User $user): bool
    {
        if ((int) $user->ID <= 0) {
            return false;
        }

        return in_array('administrator', (array) $user->roles, true);
    }
}
