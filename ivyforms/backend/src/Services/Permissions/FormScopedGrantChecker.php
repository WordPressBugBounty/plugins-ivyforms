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
 * Checks whether a user has permission keys from any form-scoped access rule.
 */
final class FormScopedGrantChecker
{
    /**
     * @param list<array<string,mixed>> $rules
     * @param \WP_User $user
     * @param list<string> $permissionKeys
     */
    public static function userHasAnyGrant(array $rules, \WP_User $user, array $permissionKeys): bool
    {
        foreach ($rules as $rule) {
            if (!is_array($rule)) {
                continue;
            }
            if (self::ruleGrantsAny($rule, $user, $permissionKeys)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<string,mixed> $rule
     * @param \WP_User $user
     * @param list<string> $permissionKeys
     */
    private static function ruleGrantsAny(array $rule, \WP_User $user, array $permissionKeys): bool
    {
        if (!self::isFormScopedRule($rule) || !self::ruleMatchesPrincipal($rule, $user)) {
            return false;
        }

        return self::ruleGrantsAnyKey($rule, $permissionKeys);
    }

    /**
     * @param array<string,mixed> $rule
     */
    private static function isFormScopedRule(array $rule): bool
    {
        $formIds = $rule['form_ids'] ?? null;

        return is_array($formIds) && $formIds !== [];
    }

    /**
     * @param array<string,mixed> $rule
     * @param \WP_User $user
     */
    private static function ruleMatchesPrincipal(array $rule, \WP_User $user): bool
    {
        $ruleType = (string) ($rule['type'] ?? '');
        if ($ruleType === 'user') {
            return AccessRuleMatcher::matchesUser($rule, $user);
        }
        if ($ruleType === 'role') {
            return AccessRuleMatcher::matchesRole($rule, $user);
        }

        return false;
    }

    /**
     * @param array<string,mixed> $rule
     * @param list<string> $permissionKeys
     */
    private static function ruleGrantsAnyKey(array $rule, array $permissionKeys): bool
    {
        $permissions = isset($rule['permissions']) && is_array($rule['permissions'])
            ? PermissionCatalog::sanitize($rule['permissions'])
            : [];

        foreach ($permissionKeys as $permissionKey) {
            if (in_array($permissionKey, $permissions, true)) {
                return true;
            }
        }

        return false;
    }
}
