<?php

namespace IvyForms\Routes\Form;

use IvyForms\Vendor\DI\Container;
use IvyForms\Vendor\DI\DependencyException;
use IvyForms\Vendor\DI\NotFoundException;
use IvyForms\Controllers\Form\AddFormController;
use IvyForms\Controllers\Form\BulkUpdateFormController;
use IvyForms\Controllers\Form\DeleteFormController;
use IvyForms\Controllers\Form\FormSubmissionController;
use IvyForms\Controllers\Form\DuplicateFormController;
use IvyForms\Controllers\Form\GetFormController;
use IvyForms\Controllers\Form\GetFormsController;
use IvyForms\Controllers\Form\SearchFormsController;
use IvyForms\Controllers\Form\UpdateFormController;
use IvyForms\Controllers\Form\DeleteFormsController;
use IvyForms\Controllers\Form\UpdateFormStarredController;
use IvyForms\Controllers\Form\UpdateFormStatusController;
use IvyForms\Controllers\Form\ValidateBeforeSubmitController;
use IvyForms\Controllers\Form\ExportFormController;
use IvyForms\Controllers\Form\ImportFormController;
use IvyForms\Infrastructure\WP\REST\RestRouteParamReader;
use IvyForms\Services\Permissions\AdminFormListPermissionService;
use IvyForms\Services\Permissions\RoutePermissionService;
use WP_REST_Server;

class Form
{
    /**
     * @throws DependencyException
     * @throws NotFoundException
     */
    public static function registerRoutes(Container $container, string $routeNamespace): void
    {
        self::registerFormCrudRoutes($container, $routeNamespace);
        self::registerFormManagementRoutes($container, $routeNamespace);
        self::registerFormSubmissionRoutes($container, $routeNamespace);
        self::registerFormImportExportRoutes($container, $routeNamespace);
    }

    /**
     * Register CRUD routes for forms (Create, Read, Update, Delete)
     *
     * @throws DependencyException
     * @throws NotFoundException
     */
    private static function registerFormCrudRoutes(Container $container, string $routeNamespace): void
    {
        $routePermissionService         = $container->get(RoutePermissionService::class);
        $adminFormListPermissionService = $container->get(AdminFormListPermissionService::class);
        // Get single form
        register_rest_route(
            $routeNamespace,
            '/form/(?P<id>\d+)',
            [
                'methods'  => WP_REST_Server::READABLE,
                'callback' => $container->get(GetFormController::class),
                'permission_callback' => static function ($request) use (
                    $adminFormListPermissionService,
                    $routePermissionService
                ) {
                    $formId = RestRouteParamReader::getPositiveIntParam($request);
                    if ($formId === null) {
                        return false;
                    }

                    // Entry-only delegates need the form structure to render entry details,
                    // so allow either forms-list access or entries access for this form.
                    return $adminFormListPermissionService->canAccessFormInAdminLists($formId)
                        || $routePermissionService->canViewEntries($formId);
                }
            ]
        );

        // Get all forms
        register_rest_route(
            $routeNamespace,
            '/form',
            [
                'methods'  => WP_REST_Server::READABLE,
                'callback' => $container->get(GetFormsController::class),
                'permission_callback' => static function () use ($routePermissionService) {
                    return $routePermissionService->canViewFormsInLists();
                }
             ]
        );

        // Add new form
        register_rest_route(
            $routeNamespace,
            '/form/add/',
            [
                'methods'  => WP_REST_Server::CREATABLE,
                'callback' => $container->get(AddFormController::class),
                'permission_callback' => static function () use ($routePermissionService) {
                    return $routePermissionService->canEditForms();
                }
             ]
        );

        // Update form
        register_rest_route(
            $routeNamespace,
            '/form/update/(?P<id>\d+)',
            [
                'methods'  => WP_REST_Server::CREATABLE,
                'callback' => $container->get(UpdateFormController::class),
                'permission_callback' => static function ($request) use ($routePermissionService) {
                    $formId = RestRouteParamReader::getPositiveIntParam($request);
                    return $routePermissionService->canEditForms($formId);
                }
             ]
        );

        // Delete single form
        register_rest_route(
            $routeNamespace,
            '/form/delete/(?P<id>\d+)',
            [
                'methods'  => WP_REST_Server::CREATABLE,
                'callback' => $container->get(DeleteFormController::class),
                'permission_callback' => static function ($request) use ($routePermissionService) {
                    $formId = RestRouteParamReader::getPositiveIntParam($request);
                    return $routePermissionService->canDeleteForms($formId);
                }
             ]
        );

        // Delete multiple forms
        register_rest_route(
            $routeNamespace,
            '/forms/delete',
            [
                'methods'  => WP_REST_Server::CREATABLE,
                'callback' => $container->get(DeleteFormsController::class),
                'permission_callback' => static function () use ($routePermissionService) {
                    return $routePermissionService->canDeleteForms();
                }
            ]
        );
    }

    /**
     * Register form management routes (search, status updates, etc.)
     *
     * @throws DependencyException
     * @throws NotFoundException
     */
    private static function registerFormManagementRoutes(Container $container, string $routeNamespace): void
    {
        $routePermissionService = $container->get(RoutePermissionService::class);
        // Search forms
        register_rest_route(
            $routeNamespace,
            '/forms/search',
            [
                'methods'  => WP_REST_Server::READABLE,
                'callback' => $container->get(SearchFormsController::class),
                'args' => [
                    'page' => ['type' => 'integer'],
                    'perPage' => ['type' => 'integer'],
                    'search' => ['type' => 'string'],
                    'orderBy' => ['type' => 'string'],
                    'order' => ['type' => 'string'],
                    'shouldGetCount' => ['type' => 'boolean'],
                ],
                'permission_callback' => static function () use ($routePermissionService) {
                    return $routePermissionService->canViewFormsInLists();
                }
            ]
        );

        // Update form starred status
        register_rest_route(
            $routeNamespace,
            '/form/update/starred/(?P<id>\d+)',
            [
                'methods'  => WP_REST_Server::CREATABLE,
                'callback' => $container->get(UpdateFormStarredController::class),
                'permission_callback' => static function ($request) use ($routePermissionService) {
                    $formId = RestRouteParamReader::getPositiveIntParam($request);
                    return $routePermissionService->canEditForms($formId);
                }
             ]
        );

        // Update form status
        register_rest_route(
            $routeNamespace,
            '/form/update/status/(?P<id>\d+)',
            [
                'methods'  => WP_REST_Server::CREATABLE,
                'callback' => $container->get(UpdateFormStatusController::class),
                'permission_callback' => static function ($request) use ($routePermissionService) {
                    $formId = RestRouteParamReader::getPositiveIntParam($request);
                    return $routePermissionService->canEditForms($formId);
                }
             ]
        );

        // Duplicate form
        register_rest_route(
            $routeNamespace,
            '/form/duplicate/(?P<id>\d+)',
            [
                'methods'  => WP_REST_Server::CREATABLE,
                'callback' => $container->get(DuplicateFormController::class),
                'permission_callback' => static function ($request) use ($routePermissionService) {
                    $formId = RestRouteParamReader::getPositiveIntParam($request);
                    return $routePermissionService->canEditForms($formId);
                }
            ]
        );

        // Bulk update forms
        register_rest_route(
            $routeNamespace,
            '/forms/batch-actions',
            [
                'methods'  => WP_REST_Server::CREATABLE,
                'callback' => [$container->get(BulkUpdateFormController::class), 'handle'],
                'permission_callback' => static function () use ($routePermissionService) {
                    return $routePermissionService->canEditForms();
                }
            ]
        );
    }

    /**
     * Register form submission routes (public-facing)
     *
     * @throws DependencyException
     * @throws NotFoundException
     */
    private static function registerFormSubmissionRoutes(Container $container, string $routeNamespace): void
    {
        // Form submission (public route)
        register_rest_route(
            $routeNamespace,
            '/form/submission/',
            [
                'methods'  => WP_REST_Server::CREATABLE,
                'callback' => $container->get(FormSubmissionController::class),
                // TODO - Add IvyForms capability - Public use
                'permission_callback' => '__return_true'
            ]
        );

        register_rest_route(
            $routeNamespace,
            '/form/validate_before_submit/',
            [
                'methods'  => WP_REST_Server::CREATABLE,
                'callback' => $container->get(ValidateBeforeSubmitController::class),
                'permission_callback' => '__return_true',
            ]
        );
    }

    /**
     * Register form import/export routes
     *
     * @throws DependencyException
     * @throws NotFoundException
     */
    private static function registerFormImportExportRoutes(Container $container, string $routeNamespace): void
    {
        $routePermissionService = $container->get(RoutePermissionService::class);

        // Export forms
        register_rest_route(
            $routeNamespace,
            '/forms/export',
            [
                'methods'  => WP_REST_Server::CREATABLE,
                'callback' => $container->get(ExportFormController::class),
                'permission_callback' => static function () use ($routePermissionService) {
                    return $routePermissionService->canExportForms();
                }
            ]
        );

        // Import forms
        register_rest_route(
            $routeNamespace,
            '/forms/import',
            [
                'methods'  => WP_REST_Server::CREATABLE,
                'callback' => $container->get(ImportFormController::class),
                'permission_callback' => static function () use ($routePermissionService) {
                    return $routePermissionService->canEditForms();
                }
            ]
        );
    }
}
