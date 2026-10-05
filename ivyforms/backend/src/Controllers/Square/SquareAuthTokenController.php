<?php

namespace IvyForms\Controllers\Square;

use IvyForms\Services\Integrations\MiddlewareSignatureService;
use IvyForms\Services\Payment\SquareOAuthService;
use IvyForms\Services\Translations\BackendStrings;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Server-to-server OAuth token delivery from ivyforms-middleware (POST only).
 */
class SquareAuthTokenController
{
    private SquareOAuthService $squareOAuthService;
    private MiddlewareSignatureService $middlewareSignatureService;

    public function __construct(
        SquareOAuthService $squareOAuthService,
        MiddlewareSignatureService $middlewareSignatureService
    ) {
        $this->squareOAuthService = $squareOAuthService;
        $this->middlewareSignatureService = $middlewareSignatureService;
    }

    public function __invoke(WP_REST_Request $data): WP_REST_Response
    {
        return $this->handle($data);
    }

    public function handle(WP_REST_Request $data): WP_REST_Response
    {
        $params = $data->get_json_params();
        if (!is_array($params)) {
            $params = $data->get_params();
        }

        if (!$this->middlewareSignatureService->verifyTokenDeliverySignature($params)) {
            return new WP_REST_Response(
                [
                    'message' => BackendStrings::getCommonStrings()['invalid_request_data'],
                    'data' => ['success' => false],
                ],
                403
            );
        }

        $result = $this->squareOAuthService->handleAuthCallback($params);

        if (!$result['success']) {
            return new WP_REST_Response(
                [
                    'message' => $result['message'] ?? BackendStrings::getPaymentStrings()['square_connect_failed'],
                    'data' => ['success' => false],
                ],
                400
            );
        }

        return new WP_REST_Response(
            [
                'message' => BackendStrings::getCommonStrings()['ok'],
                'data' => ['success' => true],
            ],
            200
        );
    }
}
