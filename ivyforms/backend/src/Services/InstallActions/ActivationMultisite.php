<?php

/**
 * Network activation
 */

namespace IvyForms\Services\InstallActions;

use IvyForms\Common\Exceptions\InvalidArgumentException;

/**
 * Class ActivationMultisite
 *
 * @package IvyForms\Services\InstallActions
 */
class ActivationMultisite
{
    /**
     * Activate the plugin for every sub-site separately
     * @throws InvalidArgumentException
     */
    public static function init(): void
    {
        global $wpdb;

        // Get current blog id
        $oldSite = $wpdb->blogid;
        // Get all blog ids
        $siteIds = $wpdb->get_col(
            $wpdb->prepare("SELECT blog_id FROM $wpdb->blogs")
        );

        foreach ($siteIds as $siteId) {
            switch_to_blog($siteId);
            // Create database table if not exists
            ActivationDatabaseHook::init();
            ActivationCapabilitiesHook::ensureAdministratorCaps();
        }
        // Returns to current blog
        switch_to_blog($oldSite);
    }
}
