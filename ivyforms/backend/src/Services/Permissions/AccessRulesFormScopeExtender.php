<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 */

namespace IvyForms\Services\Permissions;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit;
}

use IvyForms\Repository\Permissions\AccessRulesRepository;
use WP_User;

/**
 * When a delegated user creates a form while limited to specific forms, append the new form id
 * to their scoped access rules so they can continue editing it.
 */
final class AccessRulesFormScopeExtender
{
    private AccessRulesRepository $accessRulesRepository;

    private PermissionResolver $permissionResolver;

    public function __construct(
        AccessRulesRepository $accessRulesRepository,
        PermissionResolver $permissionResolver
    ) {
        $this->accessRulesRepository = $accessRulesRepository;
        $this->permissionResolver    = $permissionResolver;
    }

    public function appendCreatedFormToCurrentUserScope(int $formId): void
    {
        $user = $this->resolveCurrentUserForScopeAppend($formId);
        if ($user === null) {
            return;
        }

        $payload = $this->accessRulesRepository->getPayload();
        $rules   = $payload['rules'];
        if (!$this->appendFormIdToUserScopedRules($rules, $formId, $user)) {
            return;
        }

        $this->persistUpdatedRules($rules);
    }

    private function resolveCurrentUserForScopeAppend(int $formId): ?WP_User
    {
        if ($formId <= 0 || IvyFormsAccess::hasImplicitElevatedAccess()) {
            return null;
        }

        $user = wp_get_current_user();
        if (!$user instanceof WP_User || (int) $user->ID <= 0) {
            return null;
        }

        if (user_can($user, IvyFormsAccess::CAP_MANAGE_ALL)) {
            return null;
        }

        if ($this->permissionResolver->userCan('add_edit_forms', $user, $formId)) {
            return null;
        }

        return $user;
    }

    /**
     * @param list<array<string,mixed>> $rules
     */
    private function appendFormIdToUserScopedRules(array &$rules, int $formId, WP_User $user): bool
    {
        $changed = false;
        $now     = time();

        foreach ($rules as $index => $rule) {
            if (!is_array($rule) || !$this->ruleGrantsAddEditForUser($rule, $user)) {
                continue;
            }

            if ($this->appendFormIdToRule($rules, $index, $formId, $now)) {
                $changed = true;
            }
        }

        return $changed;
    }

    /**
     * @param list<array<string,mixed>> $rules
     */
    private function appendFormIdToRule(array &$rules, int $index, int $formId, int $updatedAt): bool
    {
        $formIds = $rules[$index]['form_ids'] ?? null;
        if (!is_array($formIds) || $formIds === []) {
            return false;
        }

        $normalizedIds = array_values(array_unique(array_map('intval', $formIds)));
        if (in_array($formId, $normalizedIds, true)) {
            return false;
        }

        $normalizedIds[]             = $formId;
        $rules[$index]['form_ids']   = $normalizedIds;
        $rules[$index]['updated_at'] = $updatedAt;

        return true;
    }

    /**
     * @param list<array<string,mixed>> $rules
     */
    private function persistUpdatedRules(array $rules): void
    {
        $previousUserIds = AccessRulesRoleCapabilitySynchronizer::extractUserIdsFromUserRules($rules);
        $this->accessRulesRepository->saveRules($rules);
        AccessRulesRoleCapabilitySynchronizer::syncFromRules($rules, $previousUserIds);
        $this->permissionResolver->invalidateCache();
    }

    /**
     * @param array<string,mixed> $rule
     */
    private function ruleGrantsAddEditForUser(array $rule, WP_User $user): bool
    {
        $permissions = isset($rule['permissions']) && is_array($rule['permissions'])
            ? PermissionCatalog::sanitize($rule['permissions'])
            : [];

        if (!in_array('add_edit_forms', $permissions, true)) {
            return false;
        }

        $ruleType = (string) ($rule['type'] ?? '');
        if ($ruleType === 'user') {
            return AccessRuleMatcher::matchesUser($rule, $user);
        }
        if ($ruleType === 'role') {
            return AccessRuleMatcher::matchesRole($rule, $user);
        }

        return false;
    }
}
