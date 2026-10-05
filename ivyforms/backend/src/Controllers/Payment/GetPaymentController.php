<?php

namespace IvyForms\Controllers\Payment;

use IvyForms\Common\Sanitizer\Sanitizer;
use IvyForms\Controllers\Controller;
use IvyForms\Services\Payment\PaymentManager;
use IvyForms\Services\Translations\BackendStrings;
use WP_REST_Request;
use WP_REST_Response;

class GetPaymentController extends Controller
{
    private PaymentManager $paymentManager;

    public function __construct(PaymentManager $paymentManager)
    {
        $this->paymentManager = $paymentManager;
    }

    public function handle(WP_REST_Request $data): WP_REST_Response
    {
        Sanitizer::verifyNonce($data->get_header('X-WP-Nonce'));
        $entryId = Sanitizer::sanitizeId($data->get_param('entryId'));

        $payment = $this->paymentManager->getPaymentByEntryId($entryId);

        return new WP_REST_Response([
            'message' => BackendStrings::getCommonStrings()['ok'],
            'data' => $payment ? $payment->toArray() : null,
        ], 200);
    }
}
