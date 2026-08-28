<?php

namespace IvyForms\Common\Permissions;

/**
 * REST API permission callbacks for IvyForms admin SPA routes.
 */
class RestPermissions
{
    /**
     * Admin SPA routes: manage forms, entries, settings, etc.
     */
    public static function canManage(): bool
    {
        return current_user_can('ivyforms_manage') || current_user_can('manage_options');
    }

    /**
     * Plugin installation routes (WordPress core capabilities).
     */
    public static function canInstallPlugins(): bool
    {
        return current_user_can('install_plugins') && current_user_can('activate_plugins');
    }
}
