<?php

namespace IvyForms\Services\Payment;

use IvyForms\Services\Integrations\MiddlewareRequestAuthService;
use IvyForms\Services\Integrations\MiddlewareUrl;
use IvyForms\Services\Settings\SettingsService;
use IvyForms\Services\Translations\BackendStrings;

/**
 * Square OAuth via IvyForms-hosted middleware (merchant never configures app keys).
 */
class SquareOAuthService
{
    private const APPLICATION_ID_TRANSIENT = 'ivyforms_square_application_id';
    private const SANDBOX_TRANSIENT = 'ivyforms_square_sandbox';

    private SettingsService $settingsService;
    private SquarePaymentService $squarePaymentService;
    private TokenEncryptionService $tokenEncryption;
    private MiddlewareRequestAuthService $middlewareRequestAuthService;
    private SquareMiddlewareClient $squareMiddlewareClient;

    public function __construct(
        SettingsService $settingsService,
        SquarePaymentService $squarePaymentService,
        TokenEncryptionService $tokenEncryption,
        MiddlewareRequestAuthService $middlewareRequestAuthService,
        SquareMiddlewareClient $squareMiddlewareClient
    ) {
        $this->settingsService = $settingsService;
        $this->squarePaymentService = $squarePaymentService;
        $this->tokenEncryption = $tokenEncryption;
        $this->middlewareRequestAuthService = $middlewareRequestAuthService;
        $this->squareMiddlewareClient = $squareMiddlewareClient;
    }

    /**
     * Public Square application ID for Web Payments SDK (from IvyForms middleware, not per-site config).
     */
    public function getPublicApplicationId(): string
    {
        $definedApplicationId = $this->readApplicationIdFromConstant();
        if ($definedApplicationId !== '') {
            return $definedApplicationId;
        }

        $cachedApplicationId = $this->readApplicationIdFromTransient();
        if ($cachedApplicationId !== '') {
            return $cachedApplicationId;
        }

        $config = $this->squareMiddlewareClient->fetchApplicationConfig();
        $applicationId = $config['applicationId'];
        if ($applicationId !== '') {
            set_transient(self::APPLICATION_ID_TRANSIENT, $applicationId, DAY_IN_SECONDS);
        }

        /**
         * Filter the public Square application ID (OAuth + Web Payments SDK).
         *
         * @since 1.0.0
         *
         * @param string $applicationId Square application ID.
         */
        return (string) apply_filters('ivyforms/payment/square_application_id', $applicationId);
    }

    private function readApplicationIdFromConstant(): string
    {
        if (!defined('IVYFORMS_SQUARE_APP_ID') || IVYFORMS_SQUARE_APP_ID === '') {
            return '';
        }

        return (string) IVYFORMS_SQUARE_APP_ID;
    }

    private function readApplicationIdFromTransient(): string
    {
        $cached = get_transient(self::APPLICATION_ID_TRANSIENT);

        return is_string($cached) ? trim($cached) : '';
    }

    /**
     * Whether middleware Square credentials target sandbox (from /square/application-config).
     */
    public function isMiddlewareSandbox(): bool
    {
        if (defined('IVYFORMS_SQUARE_AUTH_URL') && IVYFORMS_SQUARE_AUTH_URL !== '') {
            return str_contains((string) IVYFORMS_SQUARE_AUTH_URL, 'sandbox');
        }

        if (defined('IVYFORMS_SQUARE_APP_ID') && IVYFORMS_SQUARE_APP_ID !== '') {
            return str_starts_with((string) IVYFORMS_SQUARE_APP_ID, 'sandbox-sq');
        }

        $cached = get_transient(self::SANDBOX_TRANSIENT);
        if ($cached !== false) {
            return (bool) $cached;
        }

        $config = $this->squareMiddlewareClient->fetchApplicationConfig();
        set_transient(self::SANDBOX_TRANSIENT, !empty($config['sandbox']) ? 1 : 0, DAY_IN_SECONDS);

        return !empty($config['sandbox']);
    }

    /**
     * Request Square OAuth URL from ivyforms-middleware (Amelia pattern: CSRF state in middleware DB).
     *
     * @return array{success: bool, url?: string, message?: string}
     */
    public function getAuthorizationUrl(): array
    {
        $callbackUrl = rest_url('ivyforms/v1/integration/square/authorization/token');
        $authUrl = $this->squareMiddlewareClient->fetchAuthorizationUrl($callbackUrl);

        if ($authUrl === null) {
            return [
                'success' => false,
                'message' => BackendStrings::getPaymentStrings()['square_service_unavailable'],
            ];
        }

        return [
            'success' => true,
            'url'     => $authUrl,
        ];
    }

    /**
     * Persist tokens from middleware server-to-server POST (signed, no browser query params).
     *
     * @param array<string, mixed> $params
     * @return array{success: bool, message?: string}
     */
    public function handleAuthCallback(array $params): array
    {
        $accessToken = $this->sanitizeOAuthToken((string) ($params['access_token'] ?? ''));
        $refreshToken = $this->sanitizeOAuthToken((string) ($params['refresh_token'] ?? ''));
        $merchantId = sanitize_text_field((string) ($params['merchant_id'] ?? ''));
        $expiresAt = sanitize_text_field((string) ($params['expires_at'] ?? ''));

        if ($accessToken === '' || $merchantId === '') {
            return [
                'success' => false,
                'message' => BackendStrings::getPaymentStrings()['square_connect_failed'],
            ];
        }

        $encryptedAccess = $this->tokenEncryption->encrypt($accessToken);
        $encryptedRefresh = $refreshToken !== ''
            ? $this->tokenEncryption->encrypt($refreshToken)
            : '';

        $this->settingsService->setSetting('payment', 'square', [
            'connected' => true,
            'merchantId' => $merchantId,
            'accessToken' => $encryptedAccess,
            'refreshToken' => $encryptedRefresh,
            'expiresAt' => $expiresAt,
            'disconnectedByWebhook' => false,
        ]);

        $location = $this->squarePaymentService->getPrimaryLocationFromApi();
        $merchant = $this->squarePaymentService->getMerchantInfo();

        $settings = $this->squarePaymentService->getSquareSettings();
        $settings['merchantName'] = (string) ($merchant['business_name'] ?? $merchant['name'] ?? '');
        $settings['locationId'] = (string) ($location['id'] ?? '');
        $settings['locationName'] = (string) ($location['name'] ?? '');
        $settings['currency'] = (string) ($location['currency'] ?? 'USD');

        $this->settingsService->setSetting('payment', 'square', $settings);

        $config = $this->squareMiddlewareClient->fetchApplicationConfig();
        set_transient(self::SANDBOX_TRANSIENT, !empty($config['sandbox']) ? 1 : 0, DAY_IN_SECONDS);

        return ['success' => true];
    }

    public function revokeAccess(): bool
    {
        $accessToken = $this->squarePaymentService->getDecryptedAccessToken();
        if ($accessToken === '') {
            return false;
        }

        $body = wp_json_encode(['access_token' => $accessToken]);
        if (!is_string($body)) {
            return false;
        }

        $revokeUrl = MiddlewareUrl::getBaseUrl() . '/square/revoke';
        $response = wp_remote_post(
            $revokeUrl,
            [
                'headers' => $this->middlewareRequestAuthService->buildSignedHeaders($body, $revokeUrl, 'POST'),
                'body' => $body,
                'timeout' => 30,
            ]
        );

        if (is_wp_error($response)) {
            return false;
        }

        $decoded = json_decode((string) wp_remote_retrieve_body($response), true);

        return !empty($decoded['result']);
    }

    public function disconnect(): void
    {
        $this->revokeAccess();
        $this->clearSquareSettings();
        $this->notifyAdminDisconnected();
    }

    public function clearSquareSettings(): void
    {
        $this->resetSquareSettings(false);
    }

    public function clearSquareSettingsFromRevocationWebhook(): void
    {
        $this->resetSquareSettings(true);
    }

    private function resetSquareSettings(bool $disconnectedByWebhook): void
    {
        $this->settingsService->setSetting('payment', 'square', [
            'connected' => false,
            'merchantId' => '',
            'merchantName' => '',
            'locationId' => '',
            'locationName' => '',
            'currency' => '',
            'accessToken' => '',
            'refreshToken' => '',
            'expiresAt' => '',
            'disconnectedByWebhook' => $disconnectedByWebhook,
        ]);
    }

    private function sanitizeOAuthToken(string $raw): string
    {
        $token = wp_unslash($raw);
        if ($token === '' || strlen($token) > 4096) {
            return '';
        }

        return $token;
    }

    public function notifyAdminDisconnected(): void
    {
        $adminEmail = get_option('admin_email');
        if (!is_email($adminEmail)) {
            return;
        }

        $subject = BackendStrings::getPaymentStrings()['square_disconnect_email_subject'];
        $message = BackendStrings::getPaymentStrings()['square_disconnect_email_body'];

        wp_mail($adminEmail, $subject, $message);
    }
}
