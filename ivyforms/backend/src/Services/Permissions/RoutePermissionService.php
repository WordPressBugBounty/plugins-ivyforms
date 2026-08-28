<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 */

namespace IvyForms\Services\Permissions;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit;
}

use WP_User;

/**
 * Bridges stored IvyForms access rules to REST route permission callbacks.
 *
 * Fallback behavior:
 * - Multisite: super admins are always allowed.
 * - Single site: users with manage_options are always allowed.
 * - Anyone with IvyForms capabilities (including ivyforms_manage_all) is evaluated via PermissionResolver.
 * - When no rules are configured and the user has no IvyForms caps, non-elevated users are denied.
 */
class RoutePermissionService
{
    private PermissionResolver $permissionResolver;

    public function __construct(PermissionResolver $permissionResolver)
    {
        $this->permissionResolver = $permissionResolver;
    }

    public function canViewForms(?int $formId = null): bool
    {
        if (IvyFormsAccess::hasImplicitElevatedAccess()) {
            return true;
        }
        $user = $this->getCurrentUser();
        if (!$user) {
            return false;
        }

        $keys = ['view_forms_list', 'add_edit_forms', 'delete_forms'];
        if ($formId !== null) {
            return $this->userCanAnyOfKeys($user, $keys, $formId);
        }

        return $this->permissionResolver->userCanAnyOf($user, $keys);
    }

    /**
     * Gate for the All Forms list/search.
     *
     * Strictly forms permissions: entry-only delegates do not get the All Forms list. The
     * entries page reads its form filter from the entries-scoped endpoint instead, so it no
     * longer depends on this gate.
     */
    public function canViewFormsInLists(?int $formId = null): bool
    {
        return $this->canViewForms($formId);
    }

    public function canExportForms(?int $formId = null): bool
    {
        return $this->checkPermission('view_forms_list', $formId);
    }

    public function canEditForms(?int $formId = null): bool
    {
        return $this->checkPermission('add_edit_forms', $formId);
    }

    public function canDeleteForms(?int $formId = null): bool
    {
        return $this->checkPermission('delete_forms', $formId);
    }

    public function canViewEntries(?int $formId = null): bool
    {
        if (IvyFormsAccess::hasImplicitElevatedAccess()) {
            return true;
        }
        $user = $this->getCurrentUser();
        if (!$user) {
            return false;
        }

        $keys = ['view_entries_admin', 'delete_entries_admin'];
        if ($formId !== null) {
            return $this->userCanAnyOfKeys($user, $keys, $formId);
        }

        return $this->permissionResolver->userCanAnyOf($user, $keys);
    }

    public function canEditEntries(?int $formId = null): bool
    {
        return $this->checkPermission('view_entries_admin', $formId);
    }

    public function canDeleteEntries(?int $formId = null): bool
    {
        return $this->checkPermission('delete_entries_admin', $formId);
    }

    public function canAccessSettings(?int $formId = null): bool
    {
        return $this->checkPermission('access_settings_page', $formId);
    }

    private function checkPermission(string $permissionKey, ?int $formId = null): bool
    {
        if (IvyFormsAccess::hasImplicitElevatedAccess()) {
            return true;
        }
        $user = $this->getCurrentUser();
        if (!$user) {
            return false;
        }

        return $this->permissionResolver->userCanIncludingFormScoped($permissionKey, $user, $formId);
    }

    private function getCurrentUser(): ?WP_User
    {
        $user = wp_get_current_user();
        if (!$user instanceof WP_User || (int) $user->ID <= 0) {
            return null;
        }

        return $user;
    }

    /**
     * @param list<string> $permissionKeys
     */
    private function userCanAnyOfKeys(WP_User $user, array $permissionKeys, int $formId): bool
    {
        foreach ($permissionKeys as $permissionKey) {
            if ($this->permissionResolver->userCan($permissionKey, $user, $formId)) {
                return true;
            }
        }

        return false;
    }
}
