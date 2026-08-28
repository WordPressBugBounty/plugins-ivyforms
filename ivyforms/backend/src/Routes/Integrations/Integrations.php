<?php

namespace IvyForms\Routes\Integrations;

use IvyForms\Services\Permissions\IvyFormsAccess;
use IvyForms\Vendor\DI\Container;
use IvyForms\Vendor\DI\DependencyException;
use IvyForms\Vendor\DI\NotFoundException;
use IvyForms\Controllers\Integrations\InstallPluginController;
use IvyForms\Controllers\Integration\GetAllIntegrationsController;
use IvyForms\Controllers\Integration\GetIntegrationController;

/**
 * Class Integrations
 *
 * Registers REST API routes for integrations
 *
 * @package IvyForms\Routes\Integrations
 */
class Integrations
{
    /**
     * Register integrations routes
     *
     * @param Container $container
     * @param string    $routeNamespace
     *
     * @throws DependencyException
     * @throws NotFoundException
     */
    public static function registerRoutes(Container $container, string $routeNamespace): void
    {
        // Get all integrations
        register_rest_route(
            $routeNamespace,
            '/integrations',
            [
                'methods'             => 'GET',
                'callback'            => $container->get(GetAllIntegrationsController::class),
                'permission_callback' => function () {
                    return IvyFormsAccess::canAccessPluginAdminRoutes();
                },
            ]
        );

        // Get single integration
        register_rest_route(
            $routeNamespace,
            '/integrations/(?P<slug>[a-zA-Z0-9_-]+)',
            [
                'methods'             => 'GET',
                'callback'            => $container->get(GetIntegrationController::class),
                'permission_callback' => function () {
                    return IvyFormsAccess::canAccessPluginAdminRoutes();
                },
            ]
        );

        // Install plugin endpoint
        register_rest_route(
            $routeNamespace,
            '/integrations/install-plugin',
            [
                'methods'             => 'POST',
                'callback'            => [$container->get(InstallPluginController::class), 'install'],
                'permission_callback' => function () {
                    return IvyFormsAccess::canAccessPluginAdminRoutes()
                        && current_user_can('install_plugins')
                        && current_user_can('activate_plugins');
                },
            ]
        );
    }
}
