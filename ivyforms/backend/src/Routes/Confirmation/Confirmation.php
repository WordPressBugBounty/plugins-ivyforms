<?php

namespace IvyForms\Routes\Confirmation;

use IvyForms\Vendor\DI\Container;
use IvyForms\Vendor\DI\DependencyException;
use IvyForms\Vendor\DI\NotFoundException;
use IvyForms\Controllers\Confirmation\AddConfirmationController;
use IvyForms\Controllers\Confirmation\GetConfirmationsController;
use IvyForms\Controllers\Confirmation\GetConformationController;
use IvyForms\Controllers\Confirmation\UpdateConfirmationController;
use IvyForms\Infrastructure\WP\REST\RestRouteParamReader;
use IvyForms\Services\Permissions\PermissionResourceResolver;
use IvyForms\Services\Permissions\RoutePermissionService;
use WP_REST_Server;

class Confirmation
{
    /**
     * @throws DependencyException
     * @throws NotFoundException
     */
    public static function registerRoutes(Container $container, string $routeNamespace): void
    {
        $routePermissionService     = $container->get(RoutePermissionService::class);
        $permissionResourceResolver = $container->get(PermissionResourceResolver::class);

        register_rest_route(
            $routeNamespace,
            '/confirmation/(?P<id>\d+)',
            [
                'methods'  => WP_REST_Server::READABLE,
                'callback' => $container->get(GetConformationController::class),
                'permission_callback' => self::permissionForConfirmationForm(
                    $permissionResourceResolver,
                    $routePermissionService
                ),
            ]
        );

        register_rest_route(
            $routeNamespace,
            '/confirmations/(?P<id>\d+)',
            [
                'methods'  => WP_REST_Server::READABLE,
                'callback' => $container->get(GetConfirmationsController::class),
                'permission_callback' => static function ($request) use ($routePermissionService) {
                    $formId = RestRouteParamReader::getPositiveIntParam($request);
                    return $routePermissionService->canEditForms($formId);
                },
            ]
        );

        register_rest_route(
            $routeNamespace,
            '/confirmation/add/',
            [
                'methods'  => WP_REST_Server::CREATABLE,
                'callback' => $container->get(AddConfirmationController::class),
                'permission_callback' => static function ($request) use ($routePermissionService) {
                    $formId = RestRouteParamReader::getPositiveIntParam($request, 'formId');
                    return $routePermissionService->canEditForms($formId);
                }
            ]
        );

        register_rest_route(
            $routeNamespace,
            '/confirmation/update/(?P<id>\d+)',
            [
                'methods'  => WP_REST_Server::CREATABLE,
                'callback' => $container->get(UpdateConfirmationController::class),
                'permission_callback' => self::permissionForConfirmationForm(
                    $permissionResourceResolver,
                    $routePermissionService
                ),
            ]
        );
    }

    /**
     * Authorize against the confirmation's owning form, not a client-supplied formId.
     *
     * @return callable
     */
    private static function permissionForConfirmationForm(
        PermissionResourceResolver $permissionResourceResolver,
        RoutePermissionService $routePermissionService
    ): callable {
        return static function ($request) use (
            $permissionResourceResolver,
            $routePermissionService
        ): bool {
            $confirmationId = RestRouteParamReader::getPositiveIntParam($request);
            if ($confirmationId === null) {
                return false;
            }

            $formId = $permissionResourceResolver->formIdFromConfirmationId($confirmationId);
            if ($formId === null) {
                return false;
            }

            return $routePermissionService->canEditForms($formId);
        };
    }
}
