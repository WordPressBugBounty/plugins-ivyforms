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
 * Which forms appear in admin lists for the current user (All Forms, search, GET form).
 */
final class AdminFormListPermissionService
{
    private PermissionResolver $permissionResolver;

    public function __construct(PermissionResolver $permissionResolver)
    {
        $this->permissionResolver = $permissionResolver;
    }

    /**
     * Forms-list visibility is strictly forms permissions. Entries grants do NOT expose the
     * All Forms list; entry-only delegates use the entries-scoped form source instead
     * (see filterFormIdsVisibleInEntryLists()).
     */
    public function canAccessFormInAdminLists(int $formId): bool
    {
        if (IvyFormsAccess::hasImplicitElevatedAccess()) {
            return true;
        }

        $user = wp_get_current_user();
        if (!$user instanceof WP_User || (int) $user->ID <= 0) {
            return false;
        }

        if (user_can($user, IvyFormsAccess::CAP_MANAGE_ALL)) {
            return true;
        }

        $grants = $this->permissionResolver->effectiveGrants($user, $formId);

        return self::grantsIncludeAny($grants, [
            'view_forms_list',
            'add_edit_forms',
            'delete_forms',
        ]);
    }

    /**
     * @param list<int> $allFormIds
     * @return list<int>
     */
    public function filterFormIdsVisibleInAdminLists(array $allFormIds): array
    {
        $out = [];
        foreach ($allFormIds as $fid) {
            $fid = (int) $fid;
            if ($fid <= 0) {
                continue;
            }
            if ($this->canAccessFormInAdminLists($fid)) {
                $out[] = $fid;
            }
        }

        return array_values(array_unique($out));
    }

    /**
     * @param list<int> $allFormIds
     * @return list<int>
     */
    public function filterFormIdsVisibleInEntryLists(array $allFormIds): array
    {
        if (IvyFormsAccess::hasImplicitElevatedAccess()) {
            return $allFormIds;
        }

        $user = wp_get_current_user();
        if (!$user instanceof WP_User || (int) $user->ID <= 0) {
            return [];
        }

        if (user_can($user, IvyFormsAccess::CAP_MANAGE_ALL)) {
            return $allFormIds;
        }

        $out = [];
        foreach ($allFormIds as $fid) {
            $fid = (int) $fid;
            if ($fid <= 0) {
                continue;
            }
            $grants = $this->permissionResolver->effectiveGrants($user, $fid);
            if (self::grantsIncludeAny($grants, ['view_entries_admin', 'delete_entries_admin'])) {
                $out[] = $fid;
            }
        }

        return array_values(array_unique($out));
    }

    /**
     * @param list<string> $grants
     * @param list<string> $keys
     */
    private static function grantsIncludeAny(array $grants, array $keys): bool
    {
        foreach ($keys as $key) {
            if (in_array($key, $grants, true)) {
                return true;
            }
        }

        return false;
    }
}
