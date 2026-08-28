<?php

/**
 * Admin Hooks Handler
 *
 * @package IvyForms
 * @since 0.5.0
 */

namespace IvyForms\Plugin;

use IvyForms\Infrastructure\WP\MCP\McpFeatureGate;
use IvyForms\Services\Ai\AiAvailability;
use IvyForms\Services\Settings\SettingsService;

/**
 * AdminHooks
 *
 * Handles all admin-related hooks and functionality
 */
class AdminHooks
{
    /**
     * Settings Service
     */
    private ?SettingsService $settingsService = null;

    /**
     * Constructor
     *
     * @param SettingsService|null $settingsService
     */
    public function __construct(?SettingsService $settingsService = null)
    {
        $this->settingsService = $settingsService;
    }

    /**
     * Register admin hooks
     *
     * @return void
     */
    public function register(): void
    {
        add_action('admin_init', [$this, 'adminInit']);
        add_filter('admin_body_class', [$this, 'addAdminBodyClass']);
        add_action('admin_enqueue_scripts', [$this, 'adminScripts']);
    }

    /**
     * Handle admin initialization tasks
     *
     * @return void
     */
    public function adminInit(): void
    {
        $this->checkVersionAndHandleActivation();
        $this->removeAdminNotices();
        $this->maybeWarmAiAvailabilityCache();
    }

    /**
     * Warm AI capability cache outside MenuService::render() so admin UI never
     * triggers provider probes while localizing scripts.
     */
    private function maybeWarmAiAvailabilityCache(): void
    {
        if (!$this->isIvyFormsAdminPage()) {
            return;
        }

        AiAvailability::maybeRefreshTextGenerationSupport();
    }

    /**
     * Enqueue admin scripts (deactivation modal and Angie MCP server for Elementor AI integration)
     *
     * @param string $hook Current admin page hook
     * @return void
     */
    public function adminScripts(string $hook): void
    {
        // Deactivation modal on plugins page
        if ($hook === 'plugins.php') {
            wp_enqueue_script(
                'ivyforms-deactivation-modal',
                IVYFORMS_URL . 'frontend/assets/js/admin-deactivation-modal.js',
                [],
                IVYFORMS_VERSION,
                true
            );
            wp_localize_script(
                'ivyforms-deactivation-modal',
                'ivyformsData',
                [
                    'version'    => IVYFORMS_VERSION,
                    'pluginUrl'  => IVYFORMS_URL,
                    'restApiUrl' => rest_url('ivyforms/v1/deactivation-feedback'),
                    'restNonce'  => wp_create_nonce('wp_rest'),
                ]
            );
        }

        // Angie MCP client — proxy JS talks to the PHP MCP REST route.
        // Only load when Angie is active and IvyForms MCP itself is enabled.
        if (
            function_exists('is_plugin_active')
            && is_plugin_active('angie/angie.php')
            && McpFeatureGate::isEnabled()
        ) {
            $wpVersion = get_bloginfo('version');
            if (version_compare($wpVersion, '6.5', '<')) {
                return;
            }
            $mcpServerPath = IVYFORMS_PATH . '/frontend/dist/ivyforms-angie.js';
            if (!file_exists($mcpServerPath)) {
                return;
            }

            // Script modules cannot use wp_localize_script(); ensure core wpApiSettings
            // (root + nonce) exists on every admin page where Angie loads this module.
            wp_enqueue_script('wp-api-request');

            // @phpstan-ignore-next-line
            wp_enqueue_script_module(
                'ivyforms-angie-mcp',
                IVYFORMS_URL . 'frontend/dist/ivyforms-angie.js',
                array(),
                IVYFORMS_VERSION
            );
        }
    }

    /**
     * Check a plugin version and handle activation/deactivation if needed
     *
     * @return void
     */
    private function checkVersionAndHandleActivation(): void
    {
        if (!function_exists('activate_plugin')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $settingsService = new SettingsService();

        $currentVersion = IVYFORMS_VERSION;
        $savedVersion = $settingsService->getSetting('general', 'version');

        if ($savedVersion !== $currentVersion) {
            deactivate_plugins(IVYFORMS_PLUGIN_SLUG);
            activate_plugin(IVYFORMS_PLUGIN_SLUG);

            $settingsService->setSetting('general', 'version', $currentVersion);
        }
    }

    /**
     * Remove admin notices on IvyForms admin pages
     *
     * @return void
     */
    public function removeAdminNotices(): void
    {
        // Check if we're on an IvyForms admin page
        if ($this->isIvyFormsAdminPage()) {
            // Remove all admin notices
            remove_all_actions('admin_notices');
            remove_all_actions('all_admin_notices');
            // For plugins using user_admin_notices or network_admin_notices
            remove_all_actions('user_admin_notices');
            remove_all_actions('network_admin_notices');
            wp_register_style(
                'ivyforms_admin_global',
                IVYFORMS_URL . 'frontend/assets/css/global-admin.css',
                array(),
                IVYFORMS_VERSION
            );
            wp_enqueue_style('ivyforms_admin_global');
        }
    }

    /**
     * Check if current page is an IvyForms admin page
     *
     * @SuppressWarnings(PHPMD)
     *
     * @return bool
     */
    private function isIvyFormsAdminPage(): bool
    {
        if (!is_admin()) {
            return false;
        }
        // Adjust this based on your page's actual slug/identifier
        return (isset($_GET['page']) && strpos($_GET['page'], 'ivyforms') !== false);
    }

    /**
     * Add admin body class
     *
     * @param string $classes
     * @return string
     */
    public function addAdminBodyClass(string $classes): string
    {
        if ($this->settingsService === null) {
            return $classes;
        }

        if ($this->settingsService->getSetting('general', 'fullscreen')) {
            $classes .= ' ivyforms-fullscreen-mode ';
        }

        return $classes;
    }
}
