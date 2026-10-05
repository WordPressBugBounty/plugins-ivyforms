<?php

namespace IvyForms\Services\Payment;

/**
 * Parse payment-method field htmlContent JSON (Square | Stripe | PayPal).
 */
final class PaymentMethodFieldConfig
{
    /**
     * @return list<string>
     */
    public static function resolvePaymentMethods(object $paymentField): array
    {
        $raw = self::resolveRawConfig($paymentField);
        if ($raw === '') {
            return ['square'];
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return ['square'];
        }

        $configured = $decoded['paymentMethods'] ?? null;
        if (!is_array($configured)) {
            return ['square'];
        }

        return self::normalizeMethods($configured);
    }

    private static function resolveRawConfig(object $paymentField): string
    {
        if (method_exists($paymentField, 'getFieldGeneralSettings')) {
            $general = $paymentField->getFieldGeneralSettings();
            if (is_object($general) && method_exists($general, 'getHtmlContent')) {
                return (string) $general->getHtmlContent();
            }
        }

        if (!method_exists($paymentField, 'toArray')) {
            return '';
        }

        $array = $paymentField->toArray();

        return is_array($array) ? (string) ($array['htmlContent'] ?? '') : '';
    }

    /**
     * @param array<int, mixed> $configured
     * @return list<string>
     */
    private static function normalizeMethods(array $configured): array
    {
        $methods = [];
        foreach ($configured as $method) {
            if (!is_string($method)) {
                continue;
            }

            $normalized = strtolower(trim($method));
            if (self::isSupported($normalized) && !in_array($normalized, $methods, true)) {
                $methods[] = $normalized;
            }
        }

        return $methods !== [] ? $methods : ['square'];
    }

    private static function isSupported(string $method): bool
    {
        return $method === 'square' || $method === 'stripe' || $method === 'paypal';
    }

    /**
     * Primary gateway for the field (first configured provider).
     */
    public static function resolvePrimaryGatewayId(object $paymentField): string
    {
        $methods = self::resolvePaymentMethods($paymentField);

        return $methods[0];
    }

    /**
     * Provider implied by the submitted token, if that provider is allowed on the field.
     *
     * Tokens for providers not in {@see resolvePaymentMethods()} are ignored so a
     * globally registered gateway cannot take over a field that did not offer it.
     *
     * @param array<string, mixed> $rawParams
     */
    public static function resolveGatewayIdFromToken(array $rawParams, ?object $paymentField = null): ?string
    {
        $tokens = [
            'stripe' => (string) ($rawParams['paymentMethodId'] ?? '')
                . (string) ($rawParams['paymentIntentId'] ?? ''),
            'paypal' => (string) ($rawParams['paypalOrderId'] ?? ''),
            'square' => (string) ($rawParams['sourceId'] ?? ''),
        ];
        $allowed = $paymentField !== null ? self::resolvePaymentMethods($paymentField) : null;

        foreach ($tokens as $gatewayId => $token) {
            if ($token === '') {
                continue;
            }

            if ($allowed !== null && !in_array($gatewayId, $allowed, true)) {
                continue;
            }

            return $gatewayId;
        }

        return null;
    }

    public static function isRequired(object $paymentField): bool
    {
        $fromSettings = self::requiredFromGeneralSettings($paymentField);
        if ($fromSettings !== null) {
            return $fromSettings;
        }

        $fromArray = self::requiredFromArray($paymentField);

        return $fromArray ?? true;
    }

    private static function requiredFromGeneralSettings(object $paymentField): ?bool
    {
        if (!method_exists($paymentField, 'getFieldGeneralSettings')) {
            return null;
        }

        $general = $paymentField->getFieldGeneralSettings();
        if (!is_object($general) || !method_exists($general, 'isRequired')) {
            return null;
        }

        return $general->isRequired();
    }

    private static function requiredFromArray(object $paymentField): ?bool
    {
        if (!method_exists($paymentField, 'toArray')) {
            return null;
        }

        $array = $paymentField->toArray();
        if (!is_array($array) || !array_key_exists('required', $array)) {
            return null;
        }

        return (bool) $array['required'];
    }
}
