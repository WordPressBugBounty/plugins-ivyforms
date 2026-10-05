<?php

namespace IvyForms\Services\Payment;

use IvyForms\Services\Translations\BackendStrings;

/**
 * Re-reads a created Square payment and checks amount/currency/status.
 */
final class SquarePaymentLookup
{
    /**
     * @param array<string, mixed> $parsed
     * @return array<string, mixed>
     */
    public static function confirmCreated(array $parsed, int $amountCents, string $currency, string $accessToken): array
    {
        if (empty($parsed['success'])) {
            return $parsed;
        }

        $retrieved = self::retrieve((string) ($parsed['transactionId'] ?? ''), $accessToken);
        $payment = is_array($retrieved['payment'] ?? null)
            ? $retrieved['payment']
            : (is_array($parsed['payment'] ?? null) ? $parsed['payment'] : []);

        if (!self::matchesExpected($payment, $amountCents, $currency)) {
            return [
                'success' => false,
                'message' => BackendStrings::getPaymentStrings()['payment_failed'],
            ];
        }

        return $parsed;
    }

    /**
     * @return array<string, mixed>
     */
    private static function retrieve(string $paymentId, string $accessToken): array
    {
        if ($paymentId === '' || $accessToken === '') {
            return [];
        }

        $response = wp_remote_get(
            SquareEnvironment::getApiBaseUrl() . '/payments/' . rawurlencode($paymentId),
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Square-Version' => '2024-10-17',
                ],
                'timeout' => 30,
            ]
        );

        if (is_wp_error($response)) {
            return [];
        }

        $decoded = json_decode((string) wp_remote_retrieve_body($response), true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @param array<string, mixed> $payment
     */
    private static function matchesExpected(array $payment, int $amountCents, string $currency): bool
    {
        $money = is_array($payment['amount_money'] ?? null) ? $payment['amount_money'] : [];

        return (int) ($money['amount'] ?? 0) === $amountCents
            && strtoupper((string) ($money['currency'] ?? '')) === strtoupper($currency)
            && strtoupper((string) ($payment['status'] ?? '')) === 'COMPLETED';
    }
}
