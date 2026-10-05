<?php

namespace IvyForms\Services\Integrations;

/**
 * Resolves the ivyforms-middleware base URL (constant + filter).
 */
class MiddlewareUrl
{
    public static function getBaseUrl(): string
    {
        $url = defined('IVYFORMS_MIDDLEWARE_URL') ? IVYFORMS_MIDDLEWARE_URL : '';

        /**
         * Filter the IvyForms integration middleware base URL.
         *
         * @since 1.0.0
         *
         * @param string $url Middleware base URL without trailing slash.
         */
        return rtrim((string) apply_filters('ivyforms/payment/middleware_url', $url), '/');
    }
}
