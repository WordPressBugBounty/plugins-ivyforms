<?php

namespace IvyForms\Controllers\Payment;

use IvyForms\Common\Sanitizer\Sanitizer;
use IvyForms\Controllers\Controller;
use IvyForms\Factory\Payment\PaymentFactory;
use IvyForms\Services\Payment\PaymentManager;
use IvyForms\Services\Translations\BackendStrings;
use WP_REST_Request;
use WP_REST_Response;

class SearchPaymentsController extends Controller
{
    private PaymentManager $paymentManager;

    public function __construct(PaymentManager $paymentManager)
    {
        $this->paymentManager = $paymentManager;
    }

    public function handle(WP_REST_Request $data): WP_REST_Response
    {
        Sanitizer::verifyNonce($data->get_header('X-WP-Nonce'));
        $params = Sanitizer::sanitizeSearchParams($data->get_query_params());

        $result = $this->paymentManager->searchPayments($params);
        $payments = array_map(
            static function (array $row): array {
                $payment = PaymentFactory::create($row)->toArray();
                if (isset($row['formName'])) {
                    $payment['formName'] = (string) $row['formName'];
                }

                return $payment;
            },
            $result['items']
        );

        return new WP_REST_Response([
            'message' => BackendStrings::getCommonStrings()['ok'],
            'data' => [
                'data' => $payments,
                'meta' => $result['meta'],
                'filterCount' => $result['total'],
            ],
        ], 200);
    }
}
