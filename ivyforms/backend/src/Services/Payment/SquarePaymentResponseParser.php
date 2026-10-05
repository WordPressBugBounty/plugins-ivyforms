<?php

namespace IvyForms\Services\Payment;

use IvyForms\Services\Translations\BackendStrings;

/**
 * Maps Square Payments API HTTP responses to the gateway result shape.
 */
final class SquarePaymentResponseParser
{
    /**
     * @param array<string, mixed>|\WP_Error $response
     * @return array<string, mixed>
     */
    public static function fromHttp($response): array
    {
        $status = (int) wp_remote_retrieve_response_code($response);
        $decoded = json_decode((string) wp_remote_retrieve_body($response), true);

        if ($status < 200 || $status >= 300 || !is_array($decoded)) {
            return self::failure(self::extractErrorMessage($decoded));
        }

        return self::fromSuccessfulBody($decoded);
    }

    /**
     * @return array<string, mixed>
     */
    public static function failure(string $message): array
    {
        return [
            'success' => false,
            'message' => $message,
        ];
    }

    /**
     * @param array<string, mixed> $decoded
     * @return array<string, mixed>
     */
    private static function fromSuccessfulBody(array $decoded): array
    {
        $payment = is_array($decoded['payment'] ?? null) ? $decoded['payment'] : [];
        $paymentStatus = (string) ($payment['status'] ?? '');
        $transactionId = (string) ($payment['id'] ?? '');

        if ($paymentStatus !== 'COMPLETED' || $transactionId === '') {
            return self::failure(BackendStrings::getPaymentStrings()['payment_failed']);
        }

        return [
            'success' => true,
            'payment' => $payment,
            'transactionId' => $transactionId,
            'receiptUrl' => (string) ($payment['receipt_url'] ?? ''),
        ];
    }

    /**
     * @param mixed $decoded
     */
    private static function extractErrorMessage($decoded): string
    {
        $fallback = BackendStrings::getPaymentStrings()['payment_failed'];
        if (!is_array($decoded) || empty($decoded['errors'][0])) {
            return $fallback;
        }

        $firstError = $decoded['errors'][0];
        if (!empty($firstError['detail'])) {
            return (string) $firstError['detail'];
        }

        if (!empty($firstError['code'])) {
            return (string) $firstError['code'];
        }

        return $fallback;
    }
}
