<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 */

namespace IvyForms\Routes\Permissions;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit;
}

use IvyForms\Controllers\Permissions\GetAccessRulesController;
use IvyForms\Controllers\Permissions\GetPermissionsMetaController;
use IvyForms\Controllers\Permissions\SearchPermissionsUsersController;
use IvyForms\Controllers\Permissions\UpdateAccessRulesController;
use IvyForms\Services\Permissions\IvyFormsAccess;
use IvyForms\Vendor\DI\Container;
use IvyForms\Vendor\DI\DependencyException;
use IvyForms\Vendor\DI\NotFoundException;
use WP_REST_Server;

class Permissions
{
    /**
     * @throws DependencyException
     * @throws NotFoundException
     */
    public static function registerRoutes(Container $container, string $routeNamespace): void
    {
        register_rest_route(
            $routeNamespace,
            '/permissions/access-rules',
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => $container->get(GetAccessRulesController::class),
                    'permission_callback' => static function () {
                        return IvyFormsAccess::currentUserCanAccessPermissionsSettings();
                    },
                ],
                [
                    'methods'             => WP_REST_Server::EDITABLE,
                    'callback'            => $container->get(UpdateAccessRulesController::class),
                    'permission_callback' => static function () {
                        return IvyFormsAccess::currentUserCanAccessPermissionsSettings();
                    },
                ],
            ]
        );

        register_rest_route(
            $routeNamespace,
            '/permissions/meta',
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => $container->get(GetPermissionsMetaController::class),
                'permission_callback' => static function () {
                        return IvyFormsAccess::currentUserCanAccessPermissionsSettings();
                },
            ]
        );

        register_rest_route(
            $routeNamespace,
            '/permissions/users',
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => $container->get(SearchPermissionsUsersController::class),
                'permission_callback' => static function () {
                        return IvyFormsAccess::currentUserCanAccessPermissionsSettings();
                },
                'args'                => [
                    'search' => [
                        'required' => false,
                        'type'     => 'string',
                    ],
                ],
            ]
        );
    }
}
