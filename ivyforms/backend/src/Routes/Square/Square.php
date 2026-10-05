<?php

namespace IvyForms\Routes\Square;

use IvyForms\Controllers\Square\SquareAuthTokenController;
use IvyForms\Controllers\Square\SquareAuthUrlController;
use IvyForms\Controllers\Square\SquareDisconnectController;
use IvyForms\Controllers\Square\SquareLocationsController;
use IvyForms\Controllers\Square\SquareWebhookController;
use IvyForms\Vendor\DI\Container;
use WP_REST_Server;

/**
 * Square payment integration REST routes (OAuth pattern matches MailChimp).
 */
class Square
{
    public static function registerRoutes(Container $container, string $namespace): void
    {
        register_rest_route(
            $namespace,
            '/integration/square/connect',
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => $container->get(SquareAuthUrlController::class),
                'permission_callback' => static function () {
                    return current_user_can('manage_options');
                },
            ]
        );

        register_rest_route(
            $namespace,
            '/integration/square/authorization/token',
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => $container->get(SquareAuthTokenController::class),
                'permission_callback' => static function () {
                    return true;
                },
            ]
        );

        register_rest_route(
            $namespace,
            '/integration/square/disconnect',
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => $container->get(SquareDisconnectController::class),
                'permission_callback' => static function () {
                    return current_user_can('manage_options');
                },
            ]
        );

        register_rest_route(
            $namespace,
            '/integration/square/webhook',
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => $container->get(SquareWebhookController::class),
                'permission_callback' => static function () {
                    return true;
                },
            ]
        );

        register_rest_route(
            $namespace,
            '/integration/square/locations',
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => $container->get(SquareLocationsController::class),
                'permission_callback' => static function () {
                    return current_user_can('manage_options');
                },
            ]
        );
    }
}
