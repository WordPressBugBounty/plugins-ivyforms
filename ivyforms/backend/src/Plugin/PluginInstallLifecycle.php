<?php

/**
 * Plugin install, activation, and uninstall lifecycle hooks.
 *
 * @package IvyForms
 * @since 0.1.0
 */

namespace IvyForms\Plugin;

use Exception;
use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Services\InstallActions\ActivationDatabaseHook;
use IvyForms\Services\InstallActions\ActivationMultisite;
use IvyForms\Services\InstallActions\ActivationNewSiteMultisite;
use IvyForms\Services\InstallActions\ActivationCapabilitiesHook;
use IvyForms\Services\InstallActions\ActivationSettingsHook;
use IvyForms\Services\InstallActions\DeleteDatabaseHook;
use IvyForms\Services\InstallActions\DeletionMultisite;
use IvyForms\Services\Permissions\AccessRulesCapabilityBootstrap;
use IvyForms\Services\Settings\SettingsService;

/**
 * Handles WordPress activation / deactivation / uninstall callbacks.
 */
class PluginInstallLifecycle
{
    /**
     * @param bool $networkWide
     * @throws InvalidArgumentException
     */
    public static function activation(bool $networkWide): void
    {
        //Network activation
        if ($networkWide && function_exists('is_multisite') && is_multisite()) {
            ActivationMultisite::init();
        }

        ActivationDatabaseHook::init();
        ActivationCapabilitiesHook::ensureAdministratorCaps();
    }

    /**
     * Ensures IvyForms capabilities exist on the Administrator role after upgrades.
     */
    public static function maybeEnsureAdministratorCapabilities(): void
    {
        if (!is_admin()) {
            return;
        }
        ActivationCapabilitiesHook::ensureAdministratorCapsIfMissing();
        AccessRulesCapabilityBootstrap::maybeRunOnce();
    }

    /**
     * @param int $siteId
     * @throws InvalidArgumentException
     */
    public static function initNewSite(int $siteId): void
    {
        ActivationNewSiteMultisite::init($siteId);
    }

    /**
     * @throws Exception
     */
    public static function activationSettings(): void
    {
        ActivationSettingsHook::init();
    }

    /**
     * Plugin deactivation: intentionally does not delete data (see {@see self::deletion()}).
     */
    public static function deactivation(): void
    {
    }

    /**
     * Uninstall function when plugin is deleted
     * @throws InvalidArgumentException
     */
    public static function deletion(): void
    {
        $settingsService = new SettingsService();

        if ($settingsService->getSetting('general', 'delete_on_uninstall')) {
            //Network deletion
            if (
                function_exists('is_multisite') &&
                is_multisite()
            ) {
                DeletionMultisite::delete();
            }

            DeleteDatabaseHook::delete();
        }
    }
}
