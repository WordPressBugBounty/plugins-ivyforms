<?php

namespace IvyForms\Services\Integrations;

use Plugin_Upgrader;
use WP_Ajax_Upgrader_Skin;
use WP_Error;

/**
 * Plugin Installer Service
 *
 * Handles the installation and activation of WordPress plugins
 */
class PluginInstaller
{
    /**
     * Map of allowed plugins to install
     *
     * @var array<string, string>
     */
    private array $allowedPlugins = [
        'wpdatatables'  => 'wpdatatables/wpdatatables.php',
        'ameliabooking' => 'ameliabooking/ameliabooking.php',
    ];

    /**
     * Check if plugin is allowed to be installed
     *
     * @param string $pluginSlug
     *
     * @return bool
     */
    public function isPluginAllowed(string $pluginSlug): bool
    {
        return isset($this->allowedPlugins[$pluginSlug]);
    }

    /**
     * Get plugin file path
     *
     * @param string $pluginSlug
     *
     * @return string|null
     */
    public function getPluginFile(string $pluginSlug): ?string
    {
        return $this->allowedPlugins[$pluginSlug] ?? null;
    }

    /**
     * Check if plugin is already installed
     *
     * @param string $pluginFile
     *
     * @return bool
     */
    public function isPluginInstalled(string $pluginFile): bool
    {
        return file_exists(WP_PLUGIN_DIR . '/' . $pluginFile);
    }

    /**
     * Check if plugin is active
     *
     * @param string $pluginSlug
     *
     * @return bool
     */
    public function isPluginActive(string $pluginSlug): bool
    {
        if (!function_exists('is_plugin_active')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $pluginFile = self::getPluginFile($pluginSlug);

        return $pluginFile !== null && is_plugin_active($pluginFile);
    }

    /**
     * Activate a plugin
     *
     * @param string $pluginFile
     *
     * @return true|WP_Error
     */
    public function activatePlugin(string $pluginFile)
    {
        return activate_plugin($pluginFile);
    }

    /**
     * Get plugin information from WordPress.org
     *
     * @param string $pluginSlug
     *
     * @return object|WP_Error
     */
    public function getPluginInfo(string $pluginSlug)
    {
        if (!function_exists('plugins_api')) {
            require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
        }

        return plugins_api('plugin_information', [
            'slug' => $pluginSlug,
            'fields' => [
                'short_description' => false,
                'sections' => false,
                'requires' => false,
                'rating' => false,
                'ratings' => false,
                'downloaded' => false,
                'download_link' => true,
                'last_updated' => false,
                'added' => false,
                'tags' => false,
                'compatibility' => false,
                'homepage' => false,
                'donate_link' => false,
            ],
        ]);
    }

    /**
     * Install plugin from download URL
     *
     * @param string $downloadUrl
     *
     * @return bool|WP_Error
     */
    public function installPlugin(string $downloadUrl)
    {
        $this->loadRequiredFiles();

        $upgrader = new Plugin_Upgrader(new WP_Ajax_Upgrader_Skin());
        return $upgrader->install($downloadUrl);
    }

    /**
     * Load required WordPress files for plugin installation
     *
     * @return void
     */
    private function loadRequiredFiles(): void
    {
        // phpcs:disable
        if (file_exists(ABSPATH . 'wp-admin/includes/file.php')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        if (file_exists(ABSPATH . 'wp-admin/includes/misc.php')) {
            require_once ABSPATH . 'wp-admin/includes/misc.php';
        }
        if (file_exists(ABSPATH . 'wp-admin/includes/class-wp-upgrader.php')) {
            require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        }
        if (file_exists(ABSPATH . 'wp-admin/includes/plugin-install.php')) {
            require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
        }
        // phpcs:enable
    }
}
