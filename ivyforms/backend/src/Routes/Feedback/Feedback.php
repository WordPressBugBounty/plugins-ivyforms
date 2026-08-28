<?php

namespace IvyForms\Routes\Feedback;

use IvyForms\Services\Permissions\IvyFormsAccess;
use IvyForms\Vendor\DI\Container;
use IvyForms\Vendor\DI\DependencyException;
use IvyForms\Vendor\DI\NotFoundException;
use IvyForms\Controllers\Deactivation\SubmitDeactivationFeedbackController;
use WP_REST_Server;

/**
 * Class Feedback
 *
 * @package IvyForms\Routes\Feedback
 */
class Feedback
{
    /**
     * @throws DependencyException
     * @throws NotFoundException
     */
    public static function registerRoutes(Container $container, string $routeNamespace): void
    {
        // Register deactivation feedback endpoint
        register_rest_route(
            $routeNamespace,
            '/deactivation-feedback',
            [
                'methods'  => WP_REST_Server::CREATABLE,
                'callback' => $container->get(SubmitDeactivationFeedbackController::class),
                'permission_callback' => function () {
                    return IvyFormsAccess::canAccessPluginAdminRoutes();
                },
                'args' => [
                    'api_version' => [
                        'required' => false,
                        'validate_callback' => function ($param) {
                            return is_string($param);
                        }
                    ],
                    'feedback_key' => [
                        'required' => true,
                        'validate_callback' => function ($param) {
                            return is_string($param) && !empty($param);
                        }
                    ],
                    'feedback' => [
                        'required' => false,
                        'validate_callback' => function ($param) {
                            return is_string($param);
                        }
                    ]
                ]
            ]
        );
    }
}
