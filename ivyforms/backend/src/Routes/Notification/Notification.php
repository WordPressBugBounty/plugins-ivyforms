<?php

namespace IvyForms\Routes\Notification;

use IvyForms\Vendor\DI\Container;
use IvyForms\Vendor\DI\DependencyException;
use IvyForms\Vendor\DI\NotFoundException;
use IvyForms\Controllers\Notification\AddNotificationController;
use IvyForms\Controllers\Notification\DeleteNotificationController;
use IvyForms\Controllers\Notification\DeleteNotificationsController;
use IvyForms\Controllers\Notification\DuplicateNotificationController;
use IvyForms\Controllers\Notification\GetNotificationController;
use IvyForms\Controllers\Notification\GetNotificationsController;
use IvyForms\Controllers\Notification\SearchNotificationsController;
use IvyForms\Controllers\Notification\UpdateNotificationController;
use IvyForms\Infrastructure\WP\REST\RestRouteParamReader;
use IvyForms\Services\Permissions\PermissionResourceResolver;
use IvyForms\Services\Permissions\RoutePermissionService;
use WP_REST_Server;

class Notification
{
    /**
     * @throws DependencyException
     * @throws NotFoundException
     */
    public static function registerRoutes(Container $container, string $routeNamespace): void
    {
        self::registerNotificationReadRoutes($container, $routeNamespace);
        self::registerNotificationWriteRoutes($container, $routeNamespace);
    }

    /**
     * @throws DependencyException
     * @throws NotFoundException
     */
    private static function registerNotificationReadRoutes(Container $container, string $routeNamespace): void
    {
        $routePermissionService     = $container->get(RoutePermissionService::class);
        $permissionResourceResolver = $container->get(PermissionResourceResolver::class);

        register_rest_route(
            $routeNamespace,
            '/notification/(?P<id>\d+)',
            [
                'methods'  => WP_REST_Server::READABLE,
                'callback' => $container->get(GetNotificationController::class),
                'permission_callback' => self::permissionForNotificationForm(
                    $permissionResourceResolver,
                    $routePermissionService
                ),
            ]
        );

        register_rest_route(
            $routeNamespace,
            '/notifications/(?P<id>\d+)',
            [
                'methods'  => WP_REST_Server::READABLE,
                'callback' => $container->get(GetNotificationsController::class),
                'permission_callback' => static function ($request) use ($routePermissionService) {
                    $formId = RestRouteParamReader::getPositiveIntParam($request);
                    return $routePermissionService->canEditForms($formId);
                },
            ]
        );

        register_rest_route(
            $routeNamespace,
            '/notifications/search',
            [
                'methods'  => WP_REST_Server::READABLE,
                'callback' => $container->get(SearchNotificationsController::class),
                'permission_callback' => static function () use ($routePermissionService) {
                    return $routePermissionService->canEditForms();
                },
            ]
        );
    }

    /**
     * @throws DependencyException
     * @throws NotFoundException
     */
    private static function registerNotificationWriteRoutes(Container $container, string $routeNamespace): void
    {
        $routePermissionService     = $container->get(RoutePermissionService::class);
        $permissionResourceResolver = $container->get(PermissionResourceResolver::class);

        register_rest_route(
            $routeNamespace,
            '/notification/add/',
            [
                'methods'  => WP_REST_Server::CREATABLE,
                'callback' => $container->get(AddNotificationController::class),
                'permission_callback' => static function ($request) use ($routePermissionService) {
                    $formId = RestRouteParamReader::getPositiveIntParam($request, 'formId');
                    return $routePermissionService->canEditForms($formId);
                },
            ]
        );

        register_rest_route(
            $routeNamespace,
            '/notification/update/(?P<id>\d+)',
            [
                'methods'  => WP_REST_Server::CREATABLE,
                'callback' => $container->get(UpdateNotificationController::class),
                'permission_callback' => self::permissionForNotificationForm(
                    $permissionResourceResolver,
                    $routePermissionService
                ),
            ]
        );

        register_rest_route(
            $routeNamespace,
            '/notification/delete/(?P<id>\d+)',
            [
                'methods'  => WP_REST_Server::CREATABLE,
                'callback' => $container->get(DeleteNotificationController::class),
                'permission_callback' => self::permissionForNotificationForm(
                    $permissionResourceResolver,
                    $routePermissionService
                ),
            ]
        );

        register_rest_route(
            $routeNamespace,
            '/notification/duplicate/(?P<id>\d+)',
            [
                'methods'  => WP_REST_Server::CREATABLE,
                'callback' => $container->get(DuplicateNotificationController::class),
                'permission_callback' => self::permissionForNotificationForm(
                    $permissionResourceResolver,
                    $routePermissionService
                ),
            ]
        );

        register_rest_route(
            $routeNamespace,
            '/notifications/delete',
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => $container->get(DeleteNotificationsController::class),
                'permission_callback' => static function () use ($routePermissionService) {
                    return $routePermissionService->canEditForms();
                },
                'args'                => [
                    'ids' => [
                        'required' => true,
                        'type'     => 'array',
                        'items'    => ['type' => 'integer'],
                    ],
                ],
            ]
        );
    }

    /**
     * @return callable
     */
    private static function permissionForNotificationForm(
        PermissionResourceResolver $permissionResourceResolver,
        RoutePermissionService $routePermissionService
    ): callable {
        return static function ($request) use (
            $permissionResourceResolver,
            $routePermissionService
        ): bool {
            $notificationId = RestRouteParamReader::getPositiveIntParam($request);
            if ($notificationId === null) {
                return false;
            }

            $formId = $permissionResourceResolver->formIdFromNotificationId($notificationId);
            if ($formId === null) {
                return false;
            }

            return $routePermissionService->canEditForms($formId);
        };
    }
}
