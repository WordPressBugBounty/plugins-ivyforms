<?php

namespace IvyForms\Services\Payment;

use IvyForms\Services\Integrations\MiddlewareUrl;

/**
 * HTTP client for ivyforms-middleware Square endpoints.
 */
class SquareMiddlewareClient
{
    /**
     * @return array{applicationId: string, sandbox: bool}
     */
    public function fetchApplicationConfig(): array
    {
        $empty = ['applicationId' => '', 'sandbox' => true];
        $middlewareUrl = MiddlewareUrl::getBaseUrl();
        if ($middlewareUrl === '') {
            return $empty;
        }

        $response = wp_remote_get(
            $middlewareUrl . '/square/application-config',
            ['timeout' => 15]
        );

        if (is_wp_error($response) || (int) wp_remote_retrieve_response_code($response) !== 200) {
            return $empty;
        }

        $result = $this->parseResultPayload($response);
        if ($result === null) {
            return $empty;
        }

        return [
            'applicationId' => trim((string) ($result['applicationId'] ?? '')),
            'sandbox' => !empty($result['sandbox']),
        ];
    }

    public function fetchAuthorizationUrl(string $callbackUrl): ?string
    {
        $middlewareUrl = MiddlewareUrl::getBaseUrl();
        if ($middlewareUrl === '') {
            return null;
        }

        $response = wp_remote_get(
            $middlewareUrl . '/square/authorization/url?' . http_build_query(
                ['callback_url' => $callbackUrl],
                '',
                '&',
                PHP_QUERY_RFC3986
            ),
            ['timeout' => 15]
        );

        if (is_wp_error($response) || (int) wp_remote_retrieve_response_code($response) !== 200) {
            return null;
        }

        $result = $this->parseResultPayload($response);
        if ($result === null) {
            return null;
        }

        $authUrl = trim((string) ($result['authorizationUrl'] ?? ''));

        if ($authUrl === '' || ! $this->isValidAuthorizationUrl($authUrl)) {
            return null;
        }

        return $authUrl;
    }

    public function isValidAuthorizationUrl(string $url): bool
    {
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        $host = (string) wp_parse_url($url, PHP_URL_HOST);
        $query = [];
        parse_str((string) wp_parse_url($url, PHP_URL_QUERY), $query);
        $clientId = trim((string) ($query['client_id'] ?? ''));

        if ($clientId === '') {
            return false;
        }

        return $host !== '' && (
            str_contains($host, 'squareup.com')
            || str_contains($host, 'squareupsandbox.com')
        );
    }

    /**
     * @param array<string, mixed>|\WP_Error $response
     * @return array<string, mixed>|null
     */
    private function parseResultPayload($response): ?array
    {
        $decoded = json_decode((string) wp_remote_retrieve_body($response), true);
        $result = is_array($decoded) ? ($decoded['result'] ?? null) : null;

        return is_array($result) ? $result : null;
    }
}
