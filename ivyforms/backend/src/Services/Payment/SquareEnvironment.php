<?php

namespace IvyForms\Services\Payment;

use IvyForms\Plugin\Plugin;

/**
 * Resolves Square sandbox vs production endpoints from IvyForms constants or middleware config.
 */
class SquareEnvironment
{
    public static function isSandbox(): bool
    {
        if (defined('IVYFORMS_SQUARE_AUTH_URL') && IVYFORMS_SQUARE_AUTH_URL !== '') {
            return str_contains((string) IVYFORMS_SQUARE_AUTH_URL, 'sandbox');
        }

        if (defined('IVYFORMS_SQUARE_APP_ID') && IVYFORMS_SQUARE_APP_ID !== '') {
            return str_starts_with((string) IVYFORMS_SQUARE_APP_ID, 'sandbox-sq');
        }

        $plugin = Plugin::getInstance();
        if ($plugin->container !== null) {
            try {
                $squareOAuthService = $plugin->container->get(SquareOAuthService::class);

                return $squareOAuthService->isMiddlewareSandbox();
            } catch (\Throwable $e) {
                // Fall through to default.
            }
        }

        return true;
    }

    public static function getApiBaseUrl(): string
    {
        return self::isSandbox()
            ? 'https://connect.squareupsandbox.com/v2'
            : 'https://connect.squareup.com/v2';
    }

    public static function getWebPaymentsSdkUrl(): string
    {
        return self::isSandbox()
            ? 'https://sandbox.web.squarecdn.com/v1/square.js'
            : 'https://web.squarecdn.com/v1/square.js';
    }
}
