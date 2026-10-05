<?php

namespace IvyForms\Routes\Payment;

use IvyForms\Controllers\Payment\GetPaymentController;
use IvyForms\Controllers\Payment\SearchPaymentsController;
use IvyForms\Vendor\DI\Container;
use WP_REST_Server;

class Payment
{
    public static function registerRoutes(Container $container, string $namespace): void
    {
        register_rest_route(
            $namespace,
            '/payments',
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => $container->get(SearchPaymentsController::class),
                'permission_callback' => static function () {
                    return current_user_can('manage_options');
                },
            ]
        );

        register_rest_route(
            $namespace,
            '/payment/entry/(?P<entryId>\d+)',
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => $container->get(GetPaymentController::class),
                'permission_callback' => static function () {
                    return current_user_can('manage_options');
                },
            ]
        );
    }
}
