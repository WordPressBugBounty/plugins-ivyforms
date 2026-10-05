<?php

namespace IvyForms\Controllers\Square;

use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Common\Sanitizer\Sanitizer;
use IvyForms\Controllers\Controller;
use IvyForms\Services\Payment\SquareOAuthService;
use IvyForms\Services\Translations\BackendStrings;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Initiates Square OAuth (same role as MailChimpConnectController).
 */
class SquareAuthUrlController extends Controller
{
    private SquareOAuthService $squareOAuthService;

    public function __construct(SquareOAuthService $squareOAuthService)
    {
        $this->squareOAuthService = $squareOAuthService;
    }

    protected function handle(WP_REST_Request $data): WP_REST_Response
    {
        Sanitizer::verifyNonce($data->get_header('X-WP-Nonce'));

        $result = $this->squareOAuthService->getAuthorizationUrl();

        if (!$result['success']) {
            throw new InvalidArgumentException(
                $result['message'] ?? BackendStrings::getPaymentStrings()['square_auth_url_failed']
            );
        }

        return new WP_REST_Response(
            [
                'auth_url' => filter_var((string) $result['url'], FILTER_SANITIZE_URL),
            ],
            200
        );
    }
}
