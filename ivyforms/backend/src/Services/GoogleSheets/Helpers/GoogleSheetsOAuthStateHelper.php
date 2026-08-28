<?php

namespace IvyForms\Services\GoogleSheets\Helpers;

/**
 * Creates and validates one-time OAuth state tokens for Google Sheets connect flows.
 */
class GoogleSheetsOAuthStateHelper
{
    private const TRANSIENT_PREFIX = 'ivyforms_gs_oauth_';
    private const TTL_SECONDS = 900;

    public static function createState(): string
    {
        $siteUrl = self::getSiteUrl();
        $userId = get_current_user_id();
        $nonce = wp_generate_password(32, false, false);
        $token = wp_hash($nonce . '|' . $userId . '|' . $siteUrl);

        set_transient(
            self::TRANSIENT_PREFIX . $token,
            [
                'user_id' => $userId,
                'site'    => $siteUrl,
                'nonce'   => $nonce,
            ],
            self::TTL_SECONDS
        );

        return $siteUrl . '|' . $token;
    }

    public static function validateState(string $state): bool
    {
        $parsed = self::parseState($state);

        if ($parsed === null) {
            return false;
        }

        [$token, $payload] = $parsed;

        if (!self::isPayloadValid($payload) || !self::isUserAllowed($payload)) {
            return false;
        }

        delete_transient(self::TRANSIENT_PREFIX . $token);

        return true;
    }

    /**
     * @return array{0: string, 1: array<string, mixed>}|null
     */
    private static function parseState(string $state): ?array
    {
        $state = trim($state);

        if ($state === '') {
            return null;
        }

        $parts = explode('|', $state, 2);

        if (count($parts) !== 2) {
            return null;
        }

        [$siteUrl, $token] = $parts;

        if ($siteUrl !== self::getSiteUrl() || $token === '') {
            return null;
        }

        $payload = get_transient(self::TRANSIENT_PREFIX . $token);

        if (!is_array($payload)) {
            return null;
        }

        return [$token, $payload];
    }

    /**
     * @param array<string, mixed> $payload
     */
    private static function isPayloadValid(array $payload): bool
    {
        return ($payload['site'] ?? '') === self::getSiteUrl();
    }

    /**
     * @param array<string, mixed> $payload
     */
    private static function isUserAllowed(array $payload): bool
    {
        $expectedUserId = (int) ($payload['user_id'] ?? 0);
        $currentUserId = get_current_user_id();

        // The one-time transient is the primary CSRF guard for this public callback.
        // Cookie auth may be missing when the callback host differs from the admin host
        // (e.g. localhost vs ivy-cursor.test); only enforce user match when both are known.
        return !($expectedUserId > 0 && $currentUserId > 0 && $currentUserId !== $expectedUserId);
    }

    private static function getSiteUrl(): string
    {
        if (defined('IVYFORMS_SITE_URL')) {
            return (string) IVYFORMS_SITE_URL;
        }

        return (string) home_url();
    }
}
