<?php

namespace IvyForms\Controllers\Square;

use IvyForms\Common\Sanitizer\Sanitizer;
use IvyForms\Controllers\Controller;
use IvyForms\Services\Payment\SquareOAuthService;
use IvyForms\Services\Translations\BackendStrings;
use WP_REST_Request;
use WP_REST_Response;

class SquareDisconnectController extends Controller
{
    private SquareOAuthService $squareOAuthService;

    public function __construct(SquareOAuthService $squareOAuthService)
    {
        $this->squareOAuthService = $squareOAuthService;
    }

    public function handle(WP_REST_Request $data): WP_REST_Response
    {
        Sanitizer::verifyNonce($data->get_header('X-WP-Nonce'));

        $this->squareOAuthService->disconnect();

        return new WP_REST_Response(
            [
                'message' => BackendStrings::getPaymentStrings()['square_disconnected'],
                'data' => ['connected' => false],
            ],
            200
        );
    }
}
