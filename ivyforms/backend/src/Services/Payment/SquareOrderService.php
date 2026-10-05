<?php

namespace IvyForms\Services\Payment;

use IvyForms\Services\Translations\BackendStrings;

/**
 * Square Orders API and Customers API client.
 *
 * Handles order creation, cancellation, and customer lookup/creation.
 * Every payment processed by IvyForms must be backed by a Square order.
 */
class SquareOrderService
{
    private const SQUARE_VERSION = '2024-10-17';
    private const CUSTOMER_TRANSIENT_PREFIX = 'ivyforms_sq_cust_';
    private const CUSTOMER_TRANSIENT_TTL = 7 * DAY_IN_SECONDS;

    private SquarePaymentService $squarePaymentService;

    public function __construct(SquarePaymentService $squarePaymentService)
    {
        $this->squarePaymentService = $squarePaymentService;
    }

    /**
     * Create a Square order before processing payment.
     *
     * @return array{success: bool, orderId: string, message: string}
     */
    public function createOrder(
        string $locationId,
        int $amountCents,
        string $currency,
        string $idempotencyKey,
        string $formName,
        string $referenceId,
        ?string $customerId
    ): array {
        $accessToken = $this->squarePaymentService->getDecryptedAccessToken();
        if ($accessToken === '') {
            return $this->failureResult(BackendStrings::getPaymentStrings()['square_not_connected']);
        }

        $body = [
            'idempotency_key' => $idempotencyKey . '-order',
            'order'           => [
                'location_id' => $locationId,
                'reference_id' => $referenceId,
                'line_items'  => [
                    [
                        'quantity'         => '1',
                        'name'             => sprintf('Form Submission - %s', $formName),
                        'base_price_money' => [
                            'amount'   => $amountCents,
                            'currency' => strtoupper($currency),
                        ],
                    ],
                ],
            ],
        ];

        if ((string) $customerId !== '') {
            $body['order']['customer_id'] = $customerId;
        }

        $response = wp_remote_post(
            SquareEnvironment::getApiBaseUrl() . '/orders',
            [
                'headers' => $this->buildHeaders($accessToken),
                'body'    => wp_json_encode($body),
                'timeout' => 30,
            ]
        );

        if (is_wp_error($response)) {
            return $this->failureResult($response->get_error_message());
        }

        $status  = (int) wp_remote_retrieve_response_code($response);
        $decoded = json_decode((string) wp_remote_retrieve_body($response), true);

        if ($status >= 200 && $status < 300 && is_array($decoded)) {
            $orderId = (string) ($decoded['order']['id'] ?? '');
            if ($orderId === '') {
                return $this->failureResult(BackendStrings::getPaymentStrings()['payment_failed']);
            }

            return ['success' => true, 'orderId' => $orderId, 'message' => ''];
        }

        return $this->failureResult($this->extractSquareError($decoded));
    }

    /**
     * Cancel a Square order to prevent orphaned open orders after a payment failure.
     *
     * @return array{success: bool}
     */
    public function cancelOrder(string $orderId, string $locationId): array
    {
        $accessToken = $this->squarePaymentService->getDecryptedAccessToken();
        if ($accessToken === '') {
            return ['success' => false];
        }

        $version = $this->fetchOrderVersion($orderId, $accessToken);
        if ($version === null) {
            return ['success' => false];
        }

        $body = [
            'idempotency_key' => wp_generate_uuid4(),
            'order'           => [
                'location_id' => $locationId,
                'state'       => 'CANCELED',
                'version'     => $version,
            ],
        ];

        $response = wp_remote_request(
            SquareEnvironment::getApiBaseUrl() . '/orders/' . rawurlencode($orderId),
            [
                'method'  => 'PUT',
                'headers' => $this->buildHeaders($accessToken),
                'body'    => wp_json_encode($body),
                'timeout' => 15,
            ]
        );

        if (is_wp_error($response)) {
            error_log('IvyForms: failed to cancel Square order ' . $orderId . ': ' . $response->get_error_message());

            return ['success' => false];
        }

        $status = (int) wp_remote_retrieve_response_code($response);

        return ['success' => $status >= 200 && $status < 300];
    }

    private function fetchOrderVersion(string $orderId, string $accessToken): ?int
    {
        $response = wp_remote_get(
            SquareEnvironment::getApiBaseUrl() . '/orders/' . rawurlencode($orderId),
            [
                'headers' => $this->buildHeaders($accessToken),
                'timeout' => 15,
            ]
        );

        if (is_wp_error($response)) {
            error_log('IvyForms: failed to fetch Square order ' . $orderId . ': ' . $response->get_error_message());

            return null;
        }

        $status = (int) wp_remote_retrieve_response_code($response);
        $decoded = json_decode((string) wp_remote_retrieve_body($response), true);
        if ($status < 200 || $status >= 300 || !is_array($decoded)) {
            return null;
        }

        $version = $decoded['order']['version'] ?? null;

        return is_numeric($version) ? (int) $version : null;
    }

    /**
     * Find an existing Square customer by email, or create one if none exists.
     * Result is cached as a transient for 7 days to reduce API calls.
     */
    public function getOrCreateCustomerFromEmail(string $email): ?string
    {
        if ($email === '') {
            return null;
        }

        $transientKey = self::CUSTOMER_TRANSIENT_PREFIX . md5($email);
        $cached = get_transient($transientKey);
        if ($cached !== false && $cached !== '') {
            return (string) $cached;
        }

        $customerId = $this->searchCustomerByEmail($email);
        if ($customerId === null) {
            $customerId = $this->createCustomer($email);
        }

        if ($customerId !== null && $customerId !== '') {
            set_transient($transientKey, $customerId, self::CUSTOMER_TRANSIENT_TTL);
        }

        return $customerId;
    }

    /**
     * Search Square for an existing customer matching the given email address.
     */
    private function searchCustomerByEmail(string $email): ?string
    {
        $accessToken = $this->squarePaymentService->getDecryptedAccessToken();
        if ($accessToken === '') {
            return null;
        }

        $body = [
            'limit' => 1,
            'query' => [
                'filter' => [
                    'email_address' => [
                        'exact' => $email,
                    ],
                ],
            ],
        ];

        $response = wp_remote_post(
            SquareEnvironment::getApiBaseUrl() . '/customers/search',
            [
                'headers' => $this->buildHeaders($accessToken),
                'body'    => wp_json_encode($body),
                'timeout' => 15,
            ]
        );

        if (is_wp_error($response)) {
            return null;
        }

        $status  = (int) wp_remote_retrieve_response_code($response);
        $decoded = json_decode((string) wp_remote_retrieve_body($response), true);

        if ($status >= 200 && $status < 300 && is_array($decoded)) {
            $customers = $decoded['customers'] ?? [];
            if (is_array($customers) && !empty($customers[0]['id'])) {
                return (string) $customers[0]['id'];
            }
        }

        return null;
    }

    /**
     * Create a new Square customer from an email address.
     */
    private function createCustomer(string $email): ?string
    {
        $accessToken = $this->squarePaymentService->getDecryptedAccessToken();
        if ($accessToken === '') {
            return null;
        }

        $body = [
            'idempotency_key' => wp_generate_uuid4(),
            'email_address'   => $email,
        ];

        $response = wp_remote_post(
            SquareEnvironment::getApiBaseUrl() . '/customers',
            [
                'headers' => $this->buildHeaders($accessToken),
                'body'    => wp_json_encode($body),
                'timeout' => 15,
            ]
        );

        if (is_wp_error($response)) {
            return null;
        }

        $status  = (int) wp_remote_retrieve_response_code($response);
        $decoded = json_decode((string) wp_remote_retrieve_body($response), true);

        if ($status >= 200 && $status < 300 && is_array($decoded)) {
            $customerId = (string) ($decoded['customer']['id'] ?? '');

            return $customerId !== '' ? $customerId : null;
        }

        return null;
    }

    /**
     * @return array<string, string>
     */
    private function buildHeaders(string $accessToken): array
    {
        return [
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type'  => 'application/json',
            'Square-Version' => self::SQUARE_VERSION,
        ];
    }

    /**
     * @param mixed $decoded
     */
    private function extractSquareError($decoded): string
    {
        $fallback = BackendStrings::getPaymentStrings()['payment_failed'];
        if (!is_array($decoded) || empty($decoded['errors'][0])) {
            return $fallback;
        }

        $firstError = $decoded['errors'][0];
        if (!empty($firstError['detail'])) {
            return (string) $firstError['detail'];
        }

        if (!empty($firstError['code'])) {
            return (string) $firstError['code'];
        }

        return $fallback;
    }

    /**
     * @return array{success: bool, orderId: string, message: string}
     */
    private function failureResult(string $message): array
    {
        return ['success' => false, 'orderId' => '', 'message' => $message];
    }
}
