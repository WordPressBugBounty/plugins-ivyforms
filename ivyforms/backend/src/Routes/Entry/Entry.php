<?php

namespace IvyForms\Routes\Entry;

use IvyForms\Vendor\DI\Container;
use IvyForms\Vendor\DI\DependencyException;
use IvyForms\Vendor\DI\NotFoundException;
use IvyForms\Controllers\Entry\BulkUpdateEntryController;
use IvyForms\Controllers\Entry\DeleteEntriesController;
use IvyForms\Controllers\Entry\DeleteEntryController;
use IvyForms\Controllers\Entry\GetEntryController;
use IvyForms\Controllers\Entry\GetEntryCountController;
use IvyForms\Controllers\Entry\GetEntryFormsController;
use IvyForms\Controllers\Entry\SearchEntriesController;
use IvyForms\Controllers\Entry\UpdateEntryStarredController;
use IvyForms\Controllers\Entry\UpdateEntryStatusController;
use IvyForms\Infrastructure\WP\REST\RestRouteParamReader;
use IvyForms\Services\Permissions\PermissionResourceResolver;
use IvyForms\Services\Permissions\RoutePermissionService;
use WP_REST_Server;

class Entry
{
    /**
     * @throws DependencyException
     * @throws NotFoundException
     */
    public static function registerRoutes(Container $container, string $routeNamespace): void
    {
        self::registerEntryReadRoutes($container, $routeNamespace);
        self::registerEntryWriteRoutes($container, $routeNamespace);
    }

    /**
     * @throws DependencyException
     * @throws NotFoundException
     */
    private static function registerEntryReadRoutes(Container $container, string $routeNamespace): void
    {
        $routePermissionService     = $container->get(RoutePermissionService::class);
        $permissionResourceResolver = $container->get(PermissionResourceResolver::class);

        register_rest_route(
            $routeNamespace,
            '/entry/(?P<id>\d+)',
            [
                'methods'  => WP_REST_Server::READABLE,
                'callback' => $container->get(GetEntryController::class),
                'permission_callback' => self::permissionForEntryForm(
                    $permissionResourceResolver,
                    static function (int $formId) use ($routePermissionService): bool {
                        return $routePermissionService->canViewEntries($formId);
                    }
                ),
            ]
        );

        register_rest_route(
            $routeNamespace,
            '/entries/forms',
            [
                'methods'  => WP_REST_Server::READABLE,
                'callback' => $container->get(GetEntryFormsController::class),
                'permission_callback' => static function () use ($routePermissionService) {
                    return $routePermissionService->canViewEntries();
                },
            ]
        );

        register_rest_route(
            $routeNamespace,
            '/entries/search',
            [
                'methods'  => WP_REST_Server::READABLE,
                'callback' => $container->get(SearchEntriesController::class),
                'permission_callback' => static function () use ($routePermissionService) {
                    return $routePermissionService->canViewEntries();
                },
            ]
        );

        register_rest_route(
            $routeNamespace,
            '/entries/count',
            [
                'methods'  => WP_REST_Server::READABLE,
                'callback' => $container->get(GetEntryCountController::class),
                'permission_callback' => static function () use ($routePermissionService) {
                    return $routePermissionService->canViewEntries();
                },
            ]
        );
    }

    /**
     * @throws DependencyException
     * @throws NotFoundException
     */
    private static function registerEntryWriteRoutes(Container $container, string $routeNamespace): void
    {
        $routePermissionService     = $container->get(RoutePermissionService::class);
        $permissionResourceResolver = $container->get(PermissionResourceResolver::class);

        register_rest_route(
            $routeNamespace,
            '/entries/delete',
            [
                'methods'  => WP_REST_Server::CREATABLE,
                'callback' => $container->get(DeleteEntriesController::class),
                'permission_callback' => static function () use ($routePermissionService) {
                    return $routePermissionService->canDeleteEntries();
                },
            ]
        );

        register_rest_route(
            $routeNamespace,
            '/entry/delete/(?P<id>\d+)',
            [
                'methods'  => WP_REST_Server::CREATABLE,
                'callback' => $container->get(DeleteEntryController::class),
                'permission_callback' => self::permissionForEntryForm(
                    $permissionResourceResolver,
                    static function (int $formId) use ($routePermissionService): bool {
                        return $routePermissionService->canDeleteEntries($formId);
                    }
                ),
            ]
        );

        register_rest_route(
            $routeNamespace,
            '/entry/update/starred/(?P<id>\d+)',
            [
                'methods'  => WP_REST_Server::CREATABLE,
                'callback' => $container->get(UpdateEntryStarredController::class),
                'permission_callback' => self::permissionForEntryForm(
                    $permissionResourceResolver,
                    static function (int $formId) use ($routePermissionService): bool {
                        return $routePermissionService->canEditEntries($formId);
                    }
                ),
            ]
        );

        register_rest_route(
            $routeNamespace,
            '/entry/update/status/(?P<id>\d+)',
            [
                'methods'  => WP_REST_Server::CREATABLE,
                'callback' => $container->get(UpdateEntryStatusController::class),
                'permission_callback' => self::permissionForEntryForm(
                    $permissionResourceResolver,
                    static function (int $formId) use ($routePermissionService): bool {
                        return $routePermissionService->canEditEntries($formId);
                    }
                ),
            ]
        );

        register_rest_route(
            $routeNamespace,
            '/entries/batch-actions',
            [
                'methods'  => WP_REST_Server::CREATABLE,
                'callback' => [$container->get(BulkUpdateEntryController::class), 'handle'],
                'permission_callback' => static function () use ($routePermissionService) {
                    return $routePermissionService->canEditEntries();
                },
            ]
        );
    }

    /**
     * @param callable(int): bool $canAccessForm
     * @return callable
     */
    private static function permissionForEntryForm(
        PermissionResourceResolver $permissionResourceResolver,
        callable $canAccessForm
    ): callable {
        return static function ($request) use ($permissionResourceResolver, $canAccessForm): bool {
            $entryId = RestRouteParamReader::getPositiveIntParam($request);
            if ($entryId === null) {
                return false;
            }

            $formId = $permissionResourceResolver->formIdFromEntryId($entryId);
            if ($formId === null) {
                return false;
            }

            return $canAccessForm($formId);
        };
    }
}
