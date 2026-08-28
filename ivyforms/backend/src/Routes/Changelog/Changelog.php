<?php

namespace IvyForms\Routes\Changelog;

use IvyForms\Services\Permissions\IvyFormsAccess;
use IvyForms\Vendor\DI\Container;
use IvyForms\Vendor\DI\DependencyException;
use IvyForms\Vendor\DI\NotFoundException;
use IvyForms\Controllers\Changelog\GetChangelogController;
use WP_REST_Server;

class Changelog
{
    /**
     * @throws DependencyException
     * @throws NotFoundException
     */
    public static function registerRoutes(Container $container, string $routeNamespace): void
    {
        register_rest_route(
            $routeNamespace,
            '/changelog',
            [
                'methods'  => WP_REST_Server::READABLE,
                'callback' => $container->get(GetChangelogController::class),
                'permission_callback' => function () {
                    return IvyFormsAccess::canAccessPluginAdminRoutes();
                }
            ]
        );
    }
}
