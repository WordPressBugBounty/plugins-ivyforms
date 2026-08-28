<?php

namespace IvyForms\Controllers\Integrations;

use IvyForms\Common\Exceptions\ForbiddenException;
use IvyForms\Common\Sanitizer\Sanitizer;
use IvyForms\Services\Integrations\PluginInstaller;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Install Plugin Controller
 *
 * Handles installation and activation of integration plugins
 */
class InstallPluginController
{
    /**
     * @var PluginInstaller
     */
    private PluginInstaller $pluginInstaller;

    /**
     * Constructor
     *
     * @param PluginInstaller $pluginInstaller
     */
    public function __construct(PluginInstaller $pluginInstaller)
    {
        $this->pluginInstaller = $pluginInstaller;
    }

    /**
     * Install and activate a plugin
     *
     * @param WP_REST_Request<array<string, mixed>> $data
     *
     * @return WP_REST_Response
     * @throws ForbiddenException
     */
    public function install(WP_REST_Request $data): WP_REST_Response
    {
        // Verify the nonce
        Sanitizer::verifyNonce($data->get_header('X-WP-Nonce'));

        $pluginSlug = Sanitizer::sanitizeText($data->get_param('plugin_slug'));

        if (empty($pluginSlug)) {
            return $this->errorResponse('Plugin slug is required', 400);
        }

        if (!$this->pluginInstaller->isPluginAllowed($pluginSlug)) {
            return $this->errorResponse('Plugin installation not allowed', 403);
        }

        $pluginFile = $this->pluginInstaller->getPluginFile($pluginSlug);

        // Check if plugin is already installed
        if ($this->pluginInstaller->isPluginInstalled($pluginFile)) {
            return $this->handlePluginActivation($pluginFile);
        }

        // Install and activate the plugin
        return $this->handlePluginInstallation($pluginSlug, $pluginFile);
    }

    /**
     * Handle plugin activation
     *
     * @param string $pluginFile
     *
     * @return WP_REST_Response
     */
    private function handlePluginActivation(string $pluginFile): WP_REST_Response
    {
        $activated = $this->pluginInstaller->activatePlugin($pluginFile);

        if (is_wp_error($activated)) {
            return $this->errorResponse(
                'Failed to activate plugin: ' . $activated->get_error_message(),
                500
            );
        }

        return new WP_REST_Response([
            'success' => true,
            'message' => 'Plugin activated successfully',
            'action' => 'activated',
        ], 200);
    }

    /**
     * Handle plugin installation
     *
     * @param string $pluginSlug
     * @param string $pluginFile
     *
     * @return WP_REST_Response
     */
    private function handlePluginInstallation(string $pluginSlug, string $pluginFile): WP_REST_Response
    {
        // Get plugin info from WordPress.org
        $api = $this->pluginInstaller->getPluginInfo($pluginSlug);

        if (is_wp_error($api)) {
            return $this->errorResponse(
                'Failed to get plugin information: ' . $api->get_error_message(),
                500
            );
        }

        // Install the plugin
        $installed = $this->pluginInstaller->installPlugin($api->download_link);

        if (is_wp_error($installed)) {
            return $this->errorResponse(
                'Failed to install plugin: ' . $installed->get_error_message(),
                500
            );
        }

        if (!$installed) {
            return $this->errorResponse('Failed to install plugin', 500);
        }

        // Activate the plugin
        $activated = $this->pluginInstaller->activatePlugin($pluginFile);

        if (is_wp_error($activated)) {
            return new WP_REST_Response([
                'success' => false,
                'message' => 'Plugin installed but failed to activate: ' . $activated->get_error_message(),
                'action' => 'installed',
            ], 200);
        }

        return new WP_REST_Response([
            'success' => true,
            'message' => 'Plugin installed and activated successfully',
            'action' => 'installed_and_activated',
        ], 200);
    }

    /**
     * Create error response
     *
     * @param string $message
     * @param int    $status
     *
     * @return WP_REST_Response
     */
    private function errorResponse(string $message, int $status): WP_REST_Response
    {
        return new WP_REST_Response([
            'success' => false,
            'message' => $message,
        ], $status);
    }
}
