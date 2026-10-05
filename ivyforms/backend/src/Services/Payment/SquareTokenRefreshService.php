<?php

namespace IvyForms\Services\Payment;

use IvyForms\Services\Integrations\MiddlewareRequestAuthService;
use IvyForms\Services\Integrations\MiddlewareUrl;
use IvyForms\Services\Settings\SettingsService;

/**
 * Refreshes Square OAuth tokens via ivyforms-middleware.
 */
class SquareTokenRefreshService
{
    private const TOKEN_REFRESH_BUFFER_SECONDS = 432000; // 5 days

    private SettingsService $settingsService;
    private TokenEncryptionService $tokenEncryption;
    private MiddlewareRequestAuthService $middlewareRequestAuthService;

    public function __construct(
        SettingsService $settingsService,
        TokenEncryptionService $tokenEncryption,
        MiddlewareRequestAuthService $middlewareRequestAuthService
    ) {
        $this->settingsService = $settingsService;
        $this->tokenEncryption = $tokenEncryption;
        $this->middlewareRequestAuthService = $middlewareRequestAuthService;
    }

    /**
     * @param array<string, mixed> $settings
     */
    public function refreshIfNeeded(array $settings): void
    {
        if (!$this->shouldRefresh($settings)) {
            return;
        }

        $tokenData = $this->requestRefreshedTokens($settings);
        if ($tokenData === null) {
            return;
        }

        $this->persistRefreshedTokens($settings, $tokenData);
    }

    /**
     * @param array<string, mixed> $settings
     */
    private function shouldRefresh(array $settings): bool
    {
        if (empty($settings['connected'])) {
            return false;
        }

        $expiresAt = isset($settings['expiresAt']) ? strtotime((string) $settings['expiresAt']) : false;
        if ($expiresAt === false) {
            return false;
        }

        if ($expiresAt - time() > self::TOKEN_REFRESH_BUFFER_SECONDS) {
            return false;
        }

        return (string) ($settings['refreshToken'] ?? '') !== '';
    }

    /**
     * @param array<string, mixed> $settings
     * @return array<string, mixed>|null
     */
    private function requestRefreshedTokens(array $settings): ?array
    {
        $middlewareUrl = MiddlewareUrl::getBaseUrl();
        if ($middlewareUrl === '') {
            return null;
        }

        $refreshToken = $this->resolveRefreshToken($settings);

        return $this->fetchRefreshedTokenData($middlewareUrl, $refreshToken);
    }

    /**
     * @param array<string, mixed> $settings
     */
    private function resolveRefreshToken(array $settings): string
    {
        $refreshToken = (string) ($settings['refreshToken'] ?? '');
        $decryptedRefresh = $this->tokenEncryption->decrypt($refreshToken);

        return $decryptedRefresh !== '' ? $decryptedRefresh : $refreshToken;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function fetchRefreshedTokenData(string $middlewareUrl, string $refreshToken): ?array
    {
        $body = wp_json_encode(['refresh_token' => $refreshToken]);
        if (!is_string($body)) {
            return null;
        }

        $refreshUrl = $middlewareUrl . '/square/refresh';
        $response = wp_remote_post(
            $refreshUrl,
            [
                'headers' => $this->middlewareRequestAuthService->buildSignedHeaders($body, $refreshUrl, 'POST'),
                'body' => $body,
                'timeout' => 30,
            ]
        );

        return $this->parseRefreshResponse($response);
    }

    /**
     * @param array<string, mixed>|\WP_Error $response
     * @return array<string, mixed>|null
     */
    private function parseRefreshResponse($response): ?array
    {
        if (is_wp_error($response)) {
            return null;
        }

        if ((int) wp_remote_retrieve_response_code($response) !== 200) {
            return null;
        }

        $decoded = json_decode((string) wp_remote_retrieve_body($response), true);
        $tokenData = is_array($decoded) ? ($decoded['result'] ?? []) : [];

        if (empty($tokenData['access_token']) || !is_array($tokenData)) {
            return null;
        }

        return $tokenData;
    }

    /**
     * @param array<string, mixed> $settings
     * @param array<string, mixed> $tokenData Plaintext tokens from authenticated middleware response.
     */
    private function persistRefreshedTokens(array $settings, array $tokenData): void
    {
        $accessToken = (string) ($tokenData['access_token'] ?? '');
        $refreshToken = (string) ($tokenData['refresh_token'] ?? '');

        if ($accessToken === '') {
            return;
        }

        $settings['accessToken'] = $this->tokenEncryption->encrypt($accessToken);
        if ($refreshToken !== '') {
            $settings['refreshToken'] = $this->tokenEncryption->encrypt($refreshToken);
        }
        if (!empty($tokenData['expires_at'])) {
            $settings['expiresAt'] = (string) $tokenData['expires_at'];
        }

        $this->settingsService->setSetting('payment', 'square', $settings);
    }
}
