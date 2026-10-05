<?php

namespace IvyForms\Services\Payment;

/**
 * Appends payment-provider hosts to an existing Content-Security-Policy.
 *
 * A plugin must not emit a standalone CSP — that would lock the rest of the site.
 */
class PaymentCspHeaders
{
    private const SOURCES = [
        'https://web.squarecdn.com',
        'https://sandbox.web.squarecdn.com',
        'https://pci-connect.squareup.com',
        'https://pci-connect.squareupsandbox.com',
        'https://connect.squareup.com',
        'https://connect.squareupsandbox.com',
        'https://js.stripe.com',
        'https://hooks.stripe.com',
        'https://api.stripe.com',
        'https://www.paypal.com',
        'https://www.sandbox.paypal.com',
        'https://www.paypalobjects.com',
    ];

    public static function register(): void
    {
        add_filter('wp_headers', [self::class, 'appendSources']);
    }

    /**
     * @param array<string, mixed> $headers
     * @return array<string, mixed>
     */
    public static function appendSources(array $headers): array
    {
        $extra = implode(' ', self::SOURCES);

        foreach (['Content-Security-Policy', 'Content-Security-Policy-Report-Only'] as $name) {
            if (empty($headers[$name]) || !is_string($headers[$name])) {
                continue;
            }
            $headers[$name] = self::appendToPolicy($headers[$name], $extra);
        }

        return $headers;
    }

    private static function appendToPolicy(string $policy, string $extra): string
    {
        foreach (['script-src', 'frame-src', 'connect-src', 'child-src'] as $directive) {
            if (!preg_match('/' . preg_quote($directive, '/') . '\s/i', $policy)) {
                continue;
            }
            $policy = preg_replace(
                '/' . preg_quote($directive, '/') . '\s([^;]*)/i',
                $directive . ' $1 ' . $extra,
                $policy,
                1
            ) ?? $policy;
        }

        return $policy;
    }
}
