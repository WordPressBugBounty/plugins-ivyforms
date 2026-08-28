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
use IvyForms\Services\Permissions\IvyFormsAccess;

/**
 * Resolves IvyForms capability keys from stored access rules.
 *
 * v1: union of grants from all rules matching the user (role or user row) and form scope.
 * User-targeted rules are processed after role-targeted so overlapping keys behave as overrides.
 */
class PermissionResolver
{
    private AccessRulesRepository $rulesRepository;

    /** @var list<array<string,mixed>>|null */
    private ?array $cachedRules = null;

    /** @var array<string, list<string>> */
    private array $effectiveGrantsCache = [];

    public function __construct(
        AccessRulesRepository $rulesRepository
    ) {
        $this->rulesRepository = $rulesRepository;
    }

    /**
     * @param \WP_User $user
     */
    public function userCan(string $permissionKey, $user, ?int $formId = null): bool
    {
        if (!in_array($permissionKey, PermissionCatalog::keys(), true)) {
            return false;
        }

        if (!$user instanceof \WP_User || (int) $user->ID <= 0) {
            return false;
        }

        if (user_can($user, IvyFormsAccess::CAP_MANAGE_ALL)) {
            return true;
        }
        if (user_can($user, PermissionCatalog::wpCapability($permissionKey))) {
            return true;
        }

        $grants = $this->effectiveGrants($user, $formId);

        return in_array($permissionKey, $grants, true);
    }

    /**
     * True when the user has at least one key via a global rule or any form-scoped rule.
     *
     * Used for list routes (forms/entries search) where no single form id applies.
     *
     * @param \WP_User $user
     * @param list<string> $permissionKeys
     */
    public function userCanAnyOf($user, array $permissionKeys): bool
    {
        if (!$user instanceof \WP_User || (int) $user->ID <= 0) {
            return false;
        }

        if (user_can($user, IvyFormsAccess::CAP_MANAGE_ALL)) {
            return true;
        }

        foreach ($permissionKeys as $permissionKey) {
            if (!in_array($permissionKey, PermissionCatalog::keys(), true)) {
                continue;
            }
            if (user_can($user, PermissionCatalog::wpCapability($permissionKey))) {
                return true;
            }
            if ($this->userCan($permissionKey, $user, null)) {
                return true;
            }
        }

        return FormScopedGrantChecker::userHasAnyGrant($this->getRules(), $user, $permissionKeys);
    }

    /**
     * Like userCan(), but when $formId is null also allows form-scoped rules (list/single-action gates).
     *
     * @param \WP_User $user
     */
    public function userCanIncludingFormScoped(string $permissionKey, $user, ?int $formId = null): bool
    {
        if ($this->userCan($permissionKey, $user, $formId)) {
            return true;
        }

        if ($formId !== null) {
            return false;
        }

        return FormScopedGrantChecker::userHasAnyGrant($this->getRules(), $user, [$permissionKey]);
    }

    /**
     * @param \WP_User $user
     * @return list<string>
     */
    public function effectiveGrants($user, ?int $formId = null): array
    {
        if (!$user instanceof \WP_User || (int) $user->ID <= 0) {
            return [];
        }

        $cacheKey = (int) $user->ID . ':' . ($formId === null ? 'null' : (string) $formId);
        if (isset($this->effectiveGrantsCache[ $cacheKey ])) {
            return $this->effectiveGrantsCache[ $cacheKey ];
        }

        $rules = $this->getRules();
        if ($rules === []) {
            $this->effectiveGrantsCache[ $cacheKey ] = [];

            return [];
        }

        $orderedRules = $this->getRulesOrderedForMerge($rules, $user, $formId);
        if ($orderedRules === []) {
            $this->effectiveGrantsCache[ $cacheKey ] = [];

            return [];
        }

        $grants = $this->extractPermissionSet($orderedRules);
        $this->effectiveGrantsCache[ $cacheKey ] = $grants;

        return $grants;
    }

    public function invalidateCache(): void
    {
        $this->cachedRules          = null;
        $this->effectiveGrantsCache = [];
    }

    /**
     * @return list<array<string,mixed>>
     */
    private function getRules(): array
    {
        if ($this->cachedRules !== null) {
            return $this->cachedRules;
        }

        $payload           = $this->rulesRepository->getPayload();
        $this->cachedRules = $payload['rules'];

        return $this->cachedRules;
    }

    /**
     * @param list<array<string,mixed>> $rules
     * @param \WP_User $user
     * @return list<array<string,mixed>>
     */
    private function getRulesOrderedForMerge(array $rules, $user, ?int $formId): array
    {
        $roleMatches  = [];
        $userMatches  = [];
        foreach ($rules as $rule) {
            if (!AccessRuleMatcher::appliesToForm($rule, $formId)) {
                continue;
            }
            $ruleType = (string) ($rule['type'] ?? '');
            if ($ruleType === 'user' && AccessRuleMatcher::matchesUser($rule, $user)) {
                $userMatches[] = $rule;
                continue;
            }
            if ($ruleType === 'role' && AccessRuleMatcher::matchesRole($rule, $user)) {
                $roleMatches[] = $rule;
            }
        }

        return array_merge(
            AccessRuleMatcher::sortForMerge($roleMatches),
            AccessRuleMatcher::sortForMerge($userMatches)
        );
    }

    /**
     * @param list<array<string,mixed>> $rules
     * @return list<string>
     */
    private function extractPermissionSet(array $rules): array
    {
        $permissionSet = [];
        foreach ($rules as $rule) {
            $permissions = $rule['permissions'] ?? [];
            if (!is_array($permissions)) {
                continue;
            }
            foreach (PermissionCatalog::sanitize($permissions) as $permissionKey) {
                $permissionSet[$permissionKey] = true;
            }
        }

        return array_keys($permissionSet);
    }
}
