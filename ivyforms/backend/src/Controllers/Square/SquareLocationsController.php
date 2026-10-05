<?php

namespace IvyForms\Controllers\Square;

use IvyForms\Common\Sanitizer\Sanitizer;
use IvyForms\Controllers\Controller;
use IvyForms\Services\Payment\SquarePaymentService;
use IvyForms\Services\Translations\BackendStrings;
use WP_REST_Request;
use WP_REST_Response;

class SquareLocationsController extends Controller
{
    private SquarePaymentService $squarePaymentService;

    public function __construct(SquarePaymentService $squarePaymentService)
    {
        $this->squarePaymentService = $squarePaymentService;
    }

    public function handle(WP_REST_Request $data): WP_REST_Response
    {
        Sanitizer::verifyNonce($data->get_header('X-WP-Nonce'));

        $locations = $this->squarePaymentService->getLocations();

        return new WP_REST_Response(['locations' => $locations], 200);
    }
}
