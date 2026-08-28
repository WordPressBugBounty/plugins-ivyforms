<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace IvyForms\Routes\Ai;

use IvyForms\Controllers\Ai\GenerateFormController;
use IvyForms\Services\Permissions\RoutePermissionService;
use IvyForms\Vendor\DI\Container;
use IvyForms\Vendor\DI\DependencyException;
use IvyForms\Vendor\DI\NotFoundException;
use WP_REST_Server;

/**
 * AI-related REST routes.
 */
class AiRoutes
{
    /**
     * @throws DependencyException
     * @throws NotFoundException
     */
    public static function registerRoutes(Container $container, string $routeNamespace): void
    {
        $routePermissionService = $container->get(RoutePermissionService::class);

        register_rest_route(
            $routeNamespace,
            '/ai/generate-form',
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => $container->get(GenerateFormController::class),
                'permission_callback' => static function () use ($routePermissionService) {
                    return $routePermissionService->canEditForms();
                },
                'args' => [
                    'prompt' => [
                        'required' => true,
                        'type' => 'string',
                        'sanitize_callback' => 'sanitize_textarea_field',
                    ],
                ],
            ]
        );
    }
}
