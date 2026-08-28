<?php

/**
 * Network activation
 */

namespace  IvyForms\Services\InstallActions;

use IvyForms\Common\Exceptions\InvalidArgumentException;

/**
 * Class DeletionMultisite
 *
 * @package IvyForms\Services\InstallActions
 */
class DeletionMultisite
{
    /**
     * Delete the plugin tables for every sub-site separately
     * @throws InvalidArgumentException
     */
    public static function delete(): void
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
            // Delete database tables if exists
            DeleteDatabaseHook::delete();
        }
        // Returns to current blog
        switch_to_blog($oldSite);
    }
}
