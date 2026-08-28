<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 */

namespace IvyForms\Services\Permissions;

use WP_User;

/**
 * Maps validated access rules to WordPress capabilities (ivyforms_*).
 *
 * Only rules with global form scope (form_ids null) mirror ivyforms_* onto roles or users.
 * Form-scoped grants must not become user_can() caps, or PermissionResolver::userCan() would
 * short-circuit and allow actions outside the allowed forms. Scoped access stays option-based
 * only. ivyforms_access_plugin is still granted when any delegation exists for that principal
 * so the IvyForms parent admin menu can register.
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
final class AccessRulesRoleCapabilitySynchronizer
{
    /**
     * @param list<array<string,mixed>> $rules
     * @param list<int> $previousUserIds User ids that had type "user" rules before the last save (for cleanup).
     */
    public static function syncFromRules(array $rules, array $previousUserIds = []): void
    {
        $roleSync = self::aggregateRoleSyncStateBySlug($rules);
        self::stripIvyformsCapsFromNonAdministratorRoles();
        self::applyCapsToRoles($roleSync);

        $userSync = self::aggregateUserSyncStateByUserId($rules);
        self::syncUserCaps($userSync, $previousUserIds);
    }

    /**
     * @param list<array<string,mixed>> $rules
     * @return list<int>
     */
    public static function extractUserIdsFromUserRules(array $rules): array
    {
        $ids = [];
        foreach ($rules as $rule) {
            if (!is_array($rule) || ($rule['type'] ?? '') !== 'user') {
                continue;
            }
            $uid = (int) ($rule['user_id'] ?? 0);
            if ($uid > 0) {
                $ids[$uid] = true;
            }
        }

        return array_map('intval', array_keys($ids));
    }

    /**
     * @param mixed[] $rule
     */
    private static function ruleHasGlobalFormScope(array $rule): bool
    {
        return !array_key_exists('form_ids', $rule) || $rule['form_ids'] === null;
    }

    /**
     * @param array<string,mixed> $rule
     * @param 'role'|'user' $principalType
     * @param array<string|int, array{global_caps: array<string, true>, needs_plugin_cap: bool}> $accumulated
     */
    private static function accumulateDelegationRule(array $rule, string $principalType, array &$accumulated): void
    {
        if (($rule['type'] ?? '') !== $principalType) {
            return;
        }
        $principalKey = self::principalKeyForDelegationRule($rule, $principalType);
        if ($principalKey === null) {
            return;
        }
        $permissions = $rule['permissions'] ?? [];
        if (!is_array($permissions)) {
            return;
        }
        $sanitized = PermissionCatalog::sanitize($permissions);
        if ($sanitized === []) {
            return;
        }
        if (!isset($accumulated[$principalKey])) {
            $accumulated[$principalKey] = [
                'global_caps'      => [],
                'needs_plugin_cap' => false,
            ];
        }
        $accumulated[$principalKey]['needs_plugin_cap'] = true;
        if (!self::ruleHasGlobalFormScope($rule)) {
            return;
        }
        foreach ($sanitized as $logical) {
            $accumulated[$principalKey]['global_caps'][PermissionCatalog::wpCapability($logical)] = true;
        }
    }

    /**
     * @param array<string,mixed> $rule
     * @param 'role'|'user' $principalType
     *
     * @return string|int|null
     */
    private static function principalKeyForDelegationRule(array $rule, string $principalType)
    {
        if ($principalType === 'role') {
            $slug = strtolower(trim((string) ($rule['role_slug'] ?? '')));
            if ($slug === '' || $slug === 'administrator') {
                return null;
            }

            return $slug;
        }
        $userId = (int) ($rule['user_id'] ?? 0);

        return $userId > 0 ? $userId : null;
    }

    /**
     * @param list<array<string,mixed>> $rules
     * @return array<string, array{global_caps: array<string, true>, needs_plugin_cap: bool}>
     */
    private static function aggregateRoleSyncStateBySlug(array $rules): array
    {
        $byRole = [];
        foreach ($rules as $rule) {
            if (!is_array($rule)) {
                continue;
            }
            self::accumulateDelegationRule($rule, 'role', $byRole);
        }

        return $byRole;
    }

    /**
     * @param list<array<string,mixed>> $rules
     * @return array<int, array{global_caps: array<string, true>, needs_plugin_cap: bool}>
     */
    private static function aggregateUserSyncStateByUserId(array $rules): array
    {
        $byUser = [];
        foreach ($rules as $rule) {
            if (!is_array($rule)) {
                continue;
            }
            self::accumulateDelegationRule($rule, 'user', $byUser);
        }

        $normalized = [];
        foreach ($byUser as $uid => $state) {
            $normalized[(int) $uid] = $state;
        }

        return $normalized;
    }

    /**
     * @param array<int, array{global_caps: array<string, true>, needs_plugin_cap: bool}> $userSyncById
     * @param list<int> $previousUserIds
     */
    private static function syncUserCaps(array $userSyncById, array $previousUserIds): void
    {
        $newUserIds = array_map('intval', array_keys($userSyncById));
        $previousUserIds = array_map('intval', $previousUserIds);
        $affected          = array_values(array_unique(array_merge($previousUserIds, $newUserIds)));

        foreach ($affected as $userId) {
            $userId = (int) $userId;
            if ($userId <= 0) {
                continue;
            }
            clean_user_cache($userId);
            $user = new WP_User($userId);
            if ((int) $user->ID <= 0) {
                continue;
            }
            if (self::userShouldSkipUserCapSynchronization($user)) {
                continue;
            }
            if (!in_array($userId, $newUserIds, true)) {
                self::stripIvyformsCapsFromUser($user);
                clean_user_cache($userId);
                continue;
            }
            $state = $userSyncById[$userId];
            self::applyCapsToUser($user, $state['global_caps'], $state['needs_plugin_cap']);
            clean_user_cache($userId);
        }
    }

    private static function userShouldSkipUserCapSynchronization(WP_User $user): bool
    {
        return IvyFormsAccess::userHasImplicitElevatedAccess($user)
            || user_can($user, IvyFormsAccess::CAP_MANAGE_ALL);
    }

    private static function stripIvyformsCapsFromUser(WP_User $user): void
    {
        foreach (self::capsToStripFromRoles() as $cap) {
            $user->remove_cap($cap);
        }
    }

    /**
     * @param array<string, true> $globalCaps ivyforms_* from rules with form_ids null only
     */
    private static function applyCapsToUser(WP_User $user, array $globalCaps, bool $needsPluginCap): void
    {
        foreach (PermissionCatalog::keys() as $key) {
            $cap = PermissionCatalog::wpCapability($key);
            if (isset($globalCaps[$cap])) {
                $user->add_cap($cap);
                continue;
            }
            $user->remove_cap($cap);
        }
        if ($needsPluginCap) {
            $user->add_cap(IvyFormsAccess::CAP_ACCESS_PLUGIN);

            return;
        }
        $user->remove_cap(IvyFormsAccess::CAP_ACCESS_PLUGIN);
    }

    private static function stripIvyformsCapsFromNonAdministratorRoles(): void
    {
        $wpRoles = wp_roles();
        if (!$wpRoles instanceof \WP_Roles) {
            return;
        }

        $stripCaps = self::capsToStripFromRoles();

        foreach (array_keys($wpRoles->roles) as $slug) {
            if ($slug === 'administrator') {
                continue;
            }
            $role = get_role($slug);
            if ($role === null) {
                continue;
            }
            foreach ($stripCaps as $cap) {
                $role->remove_cap($cap);
            }
        }
    }

    /**
     * @return list<string>
     */
    private static function capsToStripFromRoles(): array
    {
        $caps = [];
        foreach (PermissionCatalog::keys() as $key) {
            $caps[] = PermissionCatalog::wpCapability($key);
        }
        $caps[] = IvyFormsAccess::CAP_ACCESS_PLUGIN;

        return $caps;
    }

    /**
     * @param array<string, array{global_caps: array<string, true>, needs_plugin_cap: bool}> $roleSyncBySlug
     */
    private static function applyCapsToRoles(array $roleSyncBySlug): void
    {
        foreach ($roleSyncBySlug as $slug => $state) {
            $role = get_role($slug);
            if ($role === null) {
                continue;
            }
            $globalCaps = $state['global_caps'];
            foreach (array_keys($globalCaps) as $cap) {
                $role->add_cap($cap);
            }
            if ($state['needs_plugin_cap']) {
                $role->add_cap(IvyFormsAccess::CAP_ACCESS_PLUGIN);
            }
        }
    }
}
