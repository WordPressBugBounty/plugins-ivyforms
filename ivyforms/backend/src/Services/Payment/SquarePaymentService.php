<?php

namespace IvyForms\Services\Payment;

use IvyForms\Services\Settings\SettingsService;
use IvyForms\Services\Translations\BackendStrings;

/**
 * Square Payments API client and token lifecycle.
 */
class SquarePaymentService
{
    private SettingsService $settingsService;
    private TokenEncryptionService $tokenEncryption;
    private SquareTokenRefreshService $tokenRefreshService;

    public function __construct(
        SettingsService $settingsService,
        TokenEncryptionService $tokenEncryption,
        SquareTokenRefreshService $tokenRefreshService
    ) {
        $this->settingsService = $settingsService;
        $this->tokenEncryption = $tokenEncryption;
        $this->tokenRefreshService = $tokenRefreshService;
    }

    /**
     * @param array<string, mixed> $buyerInfo
     * @return array<string, mixed>
     */
    public function processPayment(
        string $sourceId,
        string $idempotencyKey,
        int $amountCents,
        string $currency,
        string $locationId,
        string $note,
        array $buyerInfo = [],
        ?string $orderId = null,
        ?string $customerId = null
    ): array {
        $accessToken = $this->getDecryptedAccessToken();
        if ($accessToken === '') {
            return SquarePaymentResponseParser::failure(
                BackendStrings::getPaymentStrings()['square_not_connected']
            );
        }

        $response = wp_remote_post(
            SquareEnvironment::getApiBaseUrl() . '/payments',
            [
                'headers' => $this->squareJsonHeaders($accessToken),
                'body' => wp_json_encode(
                    $this->buildPaymentRequestBody(
                        $sourceId,
                        $idempotencyKey,
                        $amountCents,
                        $currency,
                        $locationId,
                        $note,
                        $buyerInfo,
                        $orderId,
                        $customerId
                    )
                ),
                'timeout' => 30,
            ]
        );

        if (is_wp_error($response)) {
            return SquarePaymentResponseParser::failure($response->get_error_message());
        }

        return SquarePaymentLookup::confirmCreated(
            SquarePaymentResponseParser::fromHttp($response),
            $amountCents,
            $currency,
            $accessToken
        );
    }

    public function getDecryptedAccessToken(): string
    {
        $this->refreshTokenIfNeeded();
        $settings = $this->getSquareSettings();
        $stored = (string) ($settings['accessToken'] ?? '');

        if ($stored === '') {
            return '';
        }

        $decrypted = $this->tokenEncryption->decrypt($stored);

        return $decrypted !== '' ? $decrypted : $stored;
    }

    public function refreshTokenIfNeeded(): void
    {
        $this->tokenRefreshService->refreshIfNeeded($this->getSquareSettings());
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getLocations(): array
    {
        $accessToken = $this->getDecryptedAccessToken();
        if ($accessToken === '') {
            return [];
        }

        $response = wp_remote_get(
            SquareEnvironment::getApiBaseUrl() . '/locations',
            [
                'headers' => $this->squareHeaders($accessToken),
                'timeout' => 30,
            ]
        );

        if (is_wp_error($response)) {
            return [];
        }

        $decoded = json_decode((string) wp_remote_retrieve_body($response), true);
        $locations = is_array($decoded) ? ($decoded['locations'] ?? []) : [];

        return is_array($locations) ? $locations : [];
    }

    /**
     * @return array<string, mixed>
     */
    public function getMerchantInfo(): array
    {
        $settings = $this->getSquareSettings();
        $merchantId = (string) ($settings['merchantId'] ?? '');
        $accessToken = $this->getDecryptedAccessToken();

        if ($merchantId === '' || $accessToken === '') {
            return [];
        }

        $response = wp_remote_get(
            SquareEnvironment::getApiBaseUrl() . '/merchants/' . rawurlencode($merchantId),
            [
                'headers' => $this->squareHeaders($accessToken),
                'timeout' => 30,
            ]
        );

        if (is_wp_error($response)) {
            return [];
        }

        $decoded = json_decode((string) wp_remote_retrieve_body($response), true);
        $merchant = is_array($decoded) ? ($decoded['merchant'] ?? []) : [];

        return is_array($merchant) ? $merchant : [];
    }

    /**
     * @return array<string, string>
     */
    private function squareHeaders(string $accessToken): array
    {
        return [
            'Authorization' => 'Bearer ' . $accessToken,
            'Square-Version' => '2024-10-17',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function squareJsonHeaders(string $accessToken): array
    {
        return $this->squareHeaders($accessToken) + ['Content-Type' => 'application/json'];
    }

    /**
     * @return array<string, mixed>
     */
    public function getSquareSettings(): array
    {
        $settings = $this->settingsService->getSetting('payment', 'square');

        return is_array($settings) ? $settings : [];
    }

    /**
     * @return array<string, mixed>
     */
    public function getPrimaryLocationFromApi(): array
    {
        $locations = $this->getLocations();
        if (empty($locations)) {
            return [];
        }

        foreach ($locations as $location) {
            if (!empty($location['status']) && $location['status'] === 'ACTIVE') {
                return $location;
            }
        }

        return $locations[0];
    }

    /**
     * @param array<string, mixed> $buyerInfo
     * @return array<string, mixed>
     */
    private function buildPaymentRequestBody(
        string $sourceId,
        string $idempotencyKey,
        int $amountCents,
        string $currency,
        string $locationId,
        string $note,
        array $buyerInfo,
        ?string $orderId = null,
        ?string $customerId = null
    ): array {
        $body = [
            'source_id' => $sourceId,
            'idempotency_key' => $idempotencyKey,
            'amount_money' => [
                'amount' => $amountCents,
                'currency' => strtoupper($currency),
            ],
            'location_id' => $locationId,
            'note' => $note,
        ];

        if ($orderId !== null && $orderId !== '') {
            $body['order_id'] = $orderId;
        }

        if ($customerId !== null && $customerId !== '') {
            $body['customer_id'] = $customerId;
        }

        if (!empty($buyerInfo['email'])) {
            $body['buyer_email_address'] = (string) $buyerInfo['email'];
        }

        if (!empty($buyerInfo['billingAddress'])) {
            $body['billing_address'] = $buyerInfo['billingAddress'];
        }

        return $body;
    }
}
