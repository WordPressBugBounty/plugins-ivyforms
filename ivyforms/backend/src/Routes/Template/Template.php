<?php

namespace IvyForms\Routes\Template;

use IvyForms\Vendor\DI\Container;
use IvyForms\Vendor\DI\DependencyException;
use IvyForms\Vendor\DI\NotFoundException;
use IvyForms\Controllers\Form\AddFormController;
use IvyForms\Controllers\Template\GetTemplatesController;
use IvyForms\Controllers\Template\GetTemplateController;
use IvyForms\Services\Permissions\RoutePermissionService;
use WP_REST_Server;

class Template
{
    /**
     * @throws DependencyException
     * @throws NotFoundException
     */
    public static function registerRoutes(Container $container, string $routeNamespace): void
    {
        $routePermissionService = $container->get(RoutePermissionService::class);
        // Get all templates
        register_rest_route(
            $routeNamespace,
            '/templates',
            [
                'methods'  => WP_REST_Server::READABLE,
                'callback' => $container->get(GetTemplatesController::class),
                'permission_callback' => static function () use ($routePermissionService) {
                    return $routePermissionService->canViewForms();
                }
            ]
        );

        // Get specific template by ID
        register_rest_route(
            $routeNamespace,
            '/template/(?P<id>[a-zA-Z0-9_-]+)',
            [
                'methods'  => WP_REST_Server::READABLE,
                'callback' => $container->get(GetTemplateController::class),
                'permission_callback' => static function () use ($routePermissionService) {
                    return $routePermissionService->canViewForms();
                }
            ]
        );

        // Create form from template
        register_rest_route(
            $routeNamespace,
            '/template/create',
            [
                'methods'  => WP_REST_Server::CREATABLE,
                'callback' => $container->get(AddFormController::class),
                'permission_callback' => static function () use ($routePermissionService) {
                    return $routePermissionService->canEditForms();
                }
            ]
        );
    }
}
