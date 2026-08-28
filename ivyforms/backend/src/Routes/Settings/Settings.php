<?php

namespace IvyForms\Routes\Settings;

use IvyForms\Vendor\DI\Container;
use IvyForms\Vendor\DI\DependencyException;
use IvyForms\Vendor\DI\NotFoundException;
use IvyForms\Controllers\Settings\GetAllSettingsController;
use IvyForms\Controllers\Settings\GetSettingController;
use IvyForms\Controllers\Settings\UpdateSettingController;
use IvyForms\Services\Permissions\RoutePermissionService;
use WP_REST_Server;

class Settings
{
    /**
     * @throws DependencyException
     * @throws NotFoundException
     */
    public static function registerRoutes(Container $container, string $routeNamespace): void
    {
        $routePermissionService = $container->get(RoutePermissionService::class);

        // Get all settings for admin page
        register_rest_route(
            $routeNamespace,
            '/settings/all',
            [
                'methods'  => WP_REST_Server::READABLE,
                'callback' => $container->get(GetAllSettingsController::class),
                'permission_callback' => static function () use ($routePermissionService) {
                    return $routePermissionService->canAccessSettings();
                }
            ]
        );

        // Get specific setting by category/option (for individual API calls)
        register_rest_route(
            $routeNamespace,
            '/setting/(?P<category>[a-zA-Z0-9_-]+)/(?P<option>[a-zA-Z0-9_-]+)',
            [
                'methods'  => WP_REST_Server::READABLE,
                'callback' => $container->get(GetSettingController::class),
                'permission_callback' => static function () use ($routePermissionService) {
                    return $routePermissionService->canAccessSettings();
                },
                'args' => [
                    'category' => [
                        'required' => true,
                        'validate_callback' => function ($param) {
                            return is_string($param) && !empty($param);
                        }
                    ],
                    'option' => [
                        'required' => true,
                        'validate_callback' => function ($param) {
                            return is_string($param) && !empty($param);
                        }
                    ]
                ]
            ]
        );

        register_rest_route(
            $routeNamespace,
            '/setting/update/',
            [
                'methods'  => WP_REST_Server::CREATABLE,
                'callback' => $container->get(UpdateSettingController::class),
                'permission_callback' => static function () use ($routePermissionService) {
                    return $routePermissionService->canAccessSettings();
                }
            ]
        );
    }
}
