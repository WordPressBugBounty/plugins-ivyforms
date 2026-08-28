<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 */

namespace IvyForms\Services\InstallActions;

use IvyForms\Services\Permissions\IvyFormsAccess;
use IvyForms\Services\Permissions\PermissionCatalog;

/**
 * Registers IvyForms-prefixed capabilities on the Administrator role.
 */
class ActivationCapabilitiesHook
{
    /**
     * Idempotent: safe to call on every activation and new subsite.
     */
    public static function ensureAdministratorCaps(): void
    {
        $role = get_role('administrator');
        if ($role === null) {
            return;
        }

        foreach (PermissionCatalog::keys() as $key) {
            $role->add_cap(PermissionCatalog::wpCapability($key));
        }

        $role->add_cap(IvyFormsAccess::CAP_MANAGE_ALL);
        $role->add_cap(IvyFormsAccess::CAP_ACCESS_PLUGIN);
    }

    /**
     * Backfill caps for sites upgraded without re-running the activation hook.
     */
    public static function ensureAdministratorCapsIfMissing(): void
    {
        $role = get_role('administrator');
        if ($role === null || $role->has_cap(IvyFormsAccess::CAP_MANAGE_ALL)) {
            return;
        }

        self::ensureAdministratorCaps();
    }
}
