<?php

namespace IvyForms\Routes\GoogleSheets;

use IvyForms\Controllers\GoogleSheets\GoogleSheetsConnectController;
use IvyForms\Controllers\GoogleSheets\GoogleSheetsOAuthCallbackController;
use IvyForms\Controllers\GoogleSheets\GoogleSheetsCreateController;
use IvyForms\Controllers\GoogleSheets\GoogleSheetsDeleteController;
use IvyForms\Controllers\GoogleSheets\GoogleSheetsDisconnectController;
use IvyForms\Controllers\GoogleSheets\GoogleSheetsListColumnsController;
use IvyForms\Controllers\GoogleSheets\GoogleSheetsListIntegrationsController;
use IvyForms\Controllers\GoogleSheets\GoogleSheetsListSpreadsheetsController;
use IvyForms\Controllers\GoogleSheets\GoogleSheetsListWorksheetsController;
use IvyForms\Controllers\GoogleSheets\GoogleSheetsStatusController;
use IvyForms\Controllers\GoogleSheets\GoogleSheetsUpdateController;
use IvyForms\Controllers\GoogleSheets\GoogleSheetsUpdateStatusController;
use IvyForms\Vendor\DI\Container;
use WP_REST_Server;

/**
 * Google Sheets integration REST routes.
 */
class GoogleSheets
{
    public static function registerRoutes(Container $container, string $namespace): void
    {
        $manageOptions = static function () {
            return current_user_can('manage_options');
        };

        register_rest_route($namespace, '/integrations/google-sheets/status', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => $container->get(GoogleSheetsStatusController::class),
            'permission_callback' => $manageOptions,
        ]);

        register_rest_route($namespace, '/integrations/google-sheets/connect', [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => $container->get(GoogleSheetsConnectController::class),
            'permission_callback' => $manageOptions,
        ]);

        register_rest_route($namespace, '/integrations/google-sheets/authorization/token', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [$container->get(GoogleSheetsOAuthCallbackController::class), 'handle'],
            'permission_callback' => static function () {
                return true;
            },
            'args'                => [
                'exchange' => [
                    'required'          => false,
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'state' => [
                    'required'          => false,
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'error' => [
                    'required'          => false,
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
            ],
        ]);

        register_rest_route($namespace, '/integrations/google-sheets/disconnect', [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => $container->get(GoogleSheetsDisconnectController::class),
            'permission_callback' => $manageOptions,
        ]);

        register_rest_route($namespace, '/integrations/google-sheets/spreadsheets', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => $container->get(GoogleSheetsListSpreadsheetsController::class),
            'permission_callback' => $manageOptions,
        ]);

        register_rest_route($namespace, '/integrations/google-sheets/worksheets', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => $container->get(GoogleSheetsListWorksheetsController::class),
            'permission_callback' => $manageOptions,
            'args'                => [
                'spreadsheet_id' => [
                    'required'          => true,
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
            ],
        ]);

        register_rest_route($namespace, '/integrations/google-sheets/columns', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => $container->get(GoogleSheetsListColumnsController::class),
            'permission_callback' => $manageOptions,
            'args'                => [
                'spreadsheet_id' => [
                    'required'          => true,
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'worksheet_name' => [
                    'required'          => true,
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
            ],
        ]);

        register_rest_route($namespace, '/integrations/google-sheets', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => $container->get(GoogleSheetsListIntegrationsController::class),
            'permission_callback' => $manageOptions,
            'args'                => [
                'form_id' => [
                    'required'          => true,
                    'type'              => 'integer',
                    'sanitize_callback' => 'absint',
                ],
            ],
        ]);

        register_rest_route($namespace, '/integrations/google-sheets/add', [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => $container->get(GoogleSheetsCreateController::class),
            'permission_callback' => $manageOptions,
        ]);

        register_rest_route($namespace, '/integrations/google-sheets/update/(?P<id>\d+)', [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => $container->get(GoogleSheetsUpdateController::class),
            'permission_callback' => $manageOptions,
            'args'                => [
                'id' => [
                    'required'          => true,
                    'type'              => 'integer',
                    'sanitize_callback' => 'absint',
                ],
            ],
        ]);

        register_rest_route($namespace, '/integrations/google-sheets/update/status/(?P<id>\d+)', [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => $container->get(GoogleSheetsUpdateStatusController::class),
            'permission_callback' => $manageOptions,
            'args'                => [
                'id' => [
                    'required'          => true,
                    'type'              => 'integer',
                    'sanitize_callback' => 'absint',
                ],
            ],
        ]);

        register_rest_route($namespace, '/integrations/google-sheets/(?P<id>\d+)', [
            'methods'             => WP_REST_Server::DELETABLE,
            'callback'            => $container->get(GoogleSheetsDeleteController::class),
            'permission_callback' => $manageOptions,
            'args'                => [
                'id' => [
                    'required'          => true,
                    'type'              => 'integer',
                    'sanitize_callback' => 'absint',
                ],
            ],
        ]);
    }
}
