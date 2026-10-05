<?php

namespace IvyForms\Services\Integrations;

/**
 * Signs outbound requests from WordPress to ivyforms-middleware.
 *
 * Canonical HMAC matches MiddlewareAuthService: timestamp, nonce, method, path, body.
 */
class MiddlewareRequestAuthService
{
    public function signRequestBody(string $body): string
    {
        $secret = $this->sharedSecret();
        if ($secret === '') {
            return '';
        }

        return hash_hmac('sha256', $body, $secret);
    }

    /**
     * @return array<string, string>
     */
    public function buildSignedHeaders(string $body, string $url = '', string $method = 'POST'): array
    {
        $secret = $this->sharedSecret();
        $path = $this->requestPath($url);
        if ($secret === '' || $path === '') {
            return ['Content-Type' => 'application/json'];
        }

        $timestamp = (string) time();
        $nonce = wp_generate_password(32, false, false);
        $canonicalRequest = implode(
            "\n",
            [
                $timestamp,
                $nonce,
                strtoupper($method),
                $path,
                $body,
            ]
        );

        return [
            'Content-Type' => 'application/json',
            'X-IvyForms-Signature' => hash_hmac('sha256', $canonicalRequest, $secret),
            'X-IvyForms-Timestamp' => $timestamp,
            'X-IvyForms-Nonce' => $nonce,
        ];
    }

    private function sharedSecret(): string
    {
        return defined('IVYFORMS_MIDDLEWARE_SHARED_SECRET')
            ? trim((string) IVYFORMS_MIDDLEWARE_SHARED_SECRET)
            : '';
    }

    private function requestPath(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH);

        return is_string($path) && $path !== '' ? $path : '';
    }
}
