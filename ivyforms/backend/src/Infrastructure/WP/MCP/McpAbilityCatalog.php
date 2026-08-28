<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

namespace IvyForms\Infrastructure\WP\MCP;

/**
 * Tiered catalog of IvyForms MCP abilities.
 *
 * Drives documentation and per-tier policy:
 * - {@see READ_ONLY_ABILITIES}: safe to expose whenever MCP is enabled.
 * - {@see WRITE_ABILITIES}: state-changing (create/update tools).
 * - {@see ABILITY_PERMISSIONS}: maps each ability to the IvyForms permission key that gates it,
 *   mirroring the REST route gates in {@see \IvyForms\Services\Permissions\RoutePermissionService}.
 *
 * Keep this list aligned with {@see IvyFormsMcpServerRegistrar::ABILITY_IDS}.
 */
class McpAbilityCatalog
{
    /**
     * Maps an MCP ability to the IvyForms permission key required to execute it.
     *
     * Mirrors the underlying REST route gates so AI assistant capabilities follow the
     * same access rules as the admin UI. Abilities without an entry (e.g. the
     * informational changelog) require only baseline MCP access.
     *
     * @var array<string, string>
     */
    private const ABILITY_PERMISSIONS = [
        // Forms (read)
        'ivyforms/get-forms'                    => 'view_forms_list',
        'ivyforms/get-form'                     => 'view_forms_list',
        // Forms (write)
        'ivyforms/create-form'                  => 'add_edit_forms',
        'ivyforms/duplicate-form'               => 'add_edit_forms',
        'ivyforms/update-form-settings'         => 'add_edit_forms',
        'ivyforms/setup-multipage-form'         => 'add_edit_forms',
        'ivyforms/add-form-page'                => 'add_edit_forms',
        'ivyforms/update-form-starred'          => 'add_edit_forms',
        'ivyforms/update-form-status'           => 'add_edit_forms',
        'ivyforms/add-form-field'               => 'add_edit_forms',
        'ivyforms/add-form-fields'              => 'add_edit_forms',
        'ivyforms/duplicate-form-field'         => 'add_edit_forms',
        'ivyforms/reorder-form-fields'          => 'add_edit_forms',
        // Entries
        'ivyforms/list-entries'                 => 'view_entries_admin',
        'ivyforms/get-entry'                    => 'view_entries_admin',
        'ivyforms/get-entry-count'              => 'view_entries_admin',
        'ivyforms/update-entry-starred'         => 'view_entries_admin',
        // Notifications (managed inside the form builder → gated as form editing)
        'ivyforms/list-notifications'           => 'add_edit_forms',
        'ivyforms/get-notification'             => 'add_edit_forms',
        'ivyforms/search-notifications'         => 'add_edit_forms',
        'ivyforms/create-notification'          => 'add_edit_forms',
        'ivyforms/update-notification'          => 'add_edit_forms',
        'ivyforms/update-notification-settings' => 'add_edit_forms',
        'ivyforms/duplicate-notification'       => 'add_edit_forms',
        // Settings
        'ivyforms/get-settings'                 => 'access_settings_page',
        'ivyforms/get-setting'                  => 'access_settings_page',
        // Templates (used to start new forms, same gate as the forms list)
        'ivyforms/list-templates'               => 'view_forms_list',
        'ivyforms/get-template'                 => 'view_forms_list',
        // Integrations (live in the settings area)
        'ivyforms/list-integrations'            => 'access_settings_page',
        'ivyforms/get-integration'              => 'access_settings_page',
        // Navigation
        'ivyforms/open-form-builder'            => 'view_forms_list',
        'ivyforms/open-entry'                   => 'view_entries_admin',
    ];

    /**
     * @var array<int, string>
     */
    public const READ_ONLY_ABILITIES = [
        'ivyforms/get-forms',
        'ivyforms/get-form',
        'ivyforms/list-entries',
        'ivyforms/get-entry',
        'ivyforms/get-entry-count',
        'ivyforms/list-notifications',
        'ivyforms/get-notification',
        'ivyforms/search-notifications',
        'ivyforms/get-settings',
        'ivyforms/get-setting',
        'ivyforms/list-templates',
        'ivyforms/get-template',
        'ivyforms/list-integrations',
        'ivyforms/get-integration',
        'ivyforms/get-changelog',
        'ivyforms/open-form-builder',
        'ivyforms/open-entry',
        // Permission management (admin only)
        'ivyforms/get-access-rules',
        'ivyforms/list-permission-keys',
    ];

    /**
     * @var array<int, string>
     */
    public const WRITE_ABILITIES = [
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
        'ivyforms/create-notification',
        'ivyforms/update-notification',
        'ivyforms/update-notification-settings',
        'ivyforms/duplicate-notification',
        'ivyforms/update-entry-starred',
        // Permission management (admin only)
        'ivyforms/grant-role-permission',
        'ivyforms/revoke-role-permission',
        'ivyforms/grant-user-permission',
        'ivyforms/revoke-user-permission',
    ];

    /**
     * Abilities that manage IvyForms permissions themselves.
     *
     * These are never delegated: they require administrator-level access
     * (see {@see McpAbilityPermissions::canExecuteAbility()}) regardless of any granted
     * IvyForms permission, mirroring the administrator-only Permissions settings tab.
     *
     * @var array<int, string>
     */
    public const ADMIN_ONLY_ABILITIES = [
        'ivyforms/get-access-rules',
        'ivyforms/list-permission-keys',
        'ivyforms/grant-role-permission',
        'ivyforms/revoke-role-permission',
        'ivyforms/grant-user-permission',
        'ivyforms/revoke-user-permission',
    ];

    public static function isKnown(string $abilityId): bool
    {
        return self::tier($abilityId) !== null;
    }

    /**
     * IvyForms permission key that gates the ability, or null when only baseline MCP
     * access is required (e.g. informational abilities like the changelog).
     */
    public static function permissionKey(string $abilityId): ?string
    {
        return self::ABILITY_PERMISSIONS[$abilityId] ?? null;
    }

    /**
     * Whether the ability manages IvyForms permissions and therefore requires
     * administrator-level access rather than a delegated permission.
     */
    public static function isAdminOnly(string $abilityId): bool
    {
        return in_array($abilityId, self::ADMIN_ONLY_ABILITIES, true);
    }

    public static function isReadOnly(string $abilityId): bool
    {
        return in_array($abilityId, self::READ_ONLY_ABILITIES, true);
    }

    public static function isWrite(string $abilityId): bool
    {
        return in_array($abilityId, self::WRITE_ABILITIES, true);
    }

    /**
     * Returns the tier (read|write) for an ability or null when unknown.
     */
    public static function tier(string $abilityId): ?string
    {
        if (self::isReadOnly($abilityId)) {
            return 'read';
        }
        if (self::isWrite($abilityId)) {
            return 'write';
        }

        return null;
    }
}
