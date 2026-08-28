<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

namespace IvyForms\Infrastructure\WP\MCP;

use IvyForms\Plugin\Plugin;
use IvyForms\Services\Permissions\IvyFormsAccess;
use IvyForms\Services\Permissions\PermissionCatalog;
use IvyForms\Services\Permissions\PermissionResolver;
use IvyForms\Vendor\DI\DependencyException;
use IvyForms\Vendor\DI\NotFoundException;
use WP_User;

/**
 * Single source of truth for MCP permission decisions.
 *
 * AI assistant capabilities follow the same access rules as the IvyForms admin: each
 * ability is gated by the IvyForms permission key that gates its underlying REST route
 * (see {@see McpAbilityCatalog::permissionKey()} and
 * {@see \IvyForms\Services\Permissions\RoutePermissionService}). Elevated users
 * (single-site `manage_options`, multisite super admins, or the `ivyforms_manage_all`
 * capability) keep full access.
 *
 * Keep capability logic centralized here — do NOT add capability checks inside
 * individual abilities.
 */
class McpAbilityPermissions
{
    private static ?PermissionResolver $permissionResolver = null;

    /**
     * Baseline permission check: whether the current user may interact with MCP at all.
     *
     * Fail-closed: denies access whenever the answer is ambiguous (no logged-in user,
     * unsupported environment). A user qualifies when they have elevated access or any
     * IvyForms permission; per-ability scoping happens in {@see canExecuteAbility()}.
     */
    public static function canUseMcp(): bool
    {
        if (!is_user_logged_in()) {
            return false;
        }

        if (IvyFormsAccess::hasImplicitElevatedAccess()) {
            return true;
        }

        $user = self::currentUser();
        if ($user === null) {
            return false;
        }

        if (user_can($user, IvyFormsAccess::CAP_MANAGE_ALL)) {
            return true;
        }

        $resolver = self::permissionResolver();
        if ($resolver === null) {
            return false;
        }

        return $resolver->userCanAnyOf($user, PermissionCatalog::keys());
    }

    /**
     * Ability-aware permission decision for `permission_callback` wiring.
     *
     * Closures registered with `wp_register_ability` should call this helper with the
     * ability id rather than reimplementing capability logic.
     */
    public static function canExecuteAbility(string $abilityId): bool
    {
        if (!McpFeatureGate::isEnabled()) {
            return false;
        }

        if (!McpAbilityCatalog::isKnown($abilityId)) {
            return false;
        }

        if (!self::canUseMcp()) {
            return false;
        }

        // Permission-management abilities are administrator-only and never delegated.
        if (McpAbilityCatalog::isAdminOnly($abilityId)) {
            return IvyFormsAccess::currentUserCanAccessPermissionsSettings();
        }

        return self::canExecuteDelegatedAbility($abilityId);
    }

    /**
     * Per-ability decision for abilities scoped to a delegated IvyForms permission.
     *
     * Elevated users (and `ivyforms_manage_all`) keep full access; everyone else must
     * hold the ability's permission key.
     */
    private static function canExecuteDelegatedAbility(string $abilityId): bool
    {
        if (IvyFormsAccess::hasImplicitElevatedAccess()) {
            return true;
        }

        $user = self::currentUser();
        if ($user === null) {
            return false;
        }

        if (user_can($user, IvyFormsAccess::CAP_MANAGE_ALL)) {
            return true;
        }

        $permissionKey = McpAbilityCatalog::permissionKey($abilityId);
        if ($permissionKey === null) {
            // Informational abilities (e.g. changelog) require only baseline MCP access.
            return true;
        }

        // No form id is available in a permission_callback, so this is a coarse gate
        // ("user holds this permission somewhere"). The underlying REST controller
        // re-checks the specific form id for defense in depth.
        $resolver = self::permissionResolver();
        if ($resolver === null) {
            return false;
        }

        return $resolver->userCanIncludingFormScoped($permissionKey, $user, null);
    }

    /**
     * Generic permission callback used by registrars that prefer a callable reference.
     */
    public static function abilityPermissionCallback(): bool
    {
        if (!McpFeatureGate::isEnabled()) {
            return false;
        }

        return self::canUseMcp();
    }

    private static function currentUser(): ?WP_User
    {
        if (!function_exists('wp_get_current_user')) {
            return null;
        }

        $user = wp_get_current_user();
        if (!$user instanceof WP_User || (int) $user->ID <= 0) {
            return null;
        }

        return $user;
    }

    private static function permissionResolver(): ?PermissionResolver
    {
        if (self::$permissionResolver !== null) {
            return self::$permissionResolver;
        }

        $plugin = Plugin::getInstance();
        if (!$plugin instanceof Plugin || $plugin->container === null) {
            return null;
        }

        try {
            self::$permissionResolver = $plugin->container->get(PermissionResolver::class);
        } catch (DependencyException | NotFoundException $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('IvyForms MCP permission resolver: ' . $e->getMessage());
            }

            return null;
        }

        return self::$permissionResolver;
    }
}
