<?php

namespace IvyForms\Services\Payment;

/**
 * Parses monetary strings from form field configuration into floats.
 */
final class MoneyParser
{
    public static function parseToFloat(string $raw): float
    {
        $trimmed = trim(str_replace(' ', '', $raw));
        if ($trimmed === '') {
            return 0.0;
        }

        $cleaned = preg_replace('/[^\d.,]/', '', $trimmed) ?? '';
        $cleaned = rtrim($cleaned, '.,');
        if ($cleaned === '' || $cleaned === '.' || $cleaned === ',') {
            return 0.0;
        }

        return self::parseCleanedAmount($cleaned);
    }

    /**
     * @param mixed $raw
     */
    public static function parseQuantity($raw): int
    {
        if ($raw === null || $raw === '') {
            return 0;
        }

        $numeric = is_numeric($raw) ? (float) $raw : (float) trim((string) $raw);

        if (!is_finite($numeric)) {
            return 0;
        }

        return (int) floor($numeric);
    }

    private static function parseCleanedAmount(string $cleaned): float
    {
        $lastComma = strrpos($cleaned, ',');
        $lastDot = strrpos($cleaned, '.');
        $decIndex = max($lastComma === false ? -1 : $lastComma, $lastDot === false ? -1 : $lastDot);

        if ($decIndex === -1) {
            $digits = preg_replace('/[^\d]/', '', $cleaned) ?? '0';
            $amount = (float) $digits;

            return is_finite($amount) ? $amount : 0.0;
        }

        $intPart = substr($cleaned, 0, $decIndex);
        $fracPart = preg_replace('/[^\d]/', '', substr($cleaned, $decIndex + 1)) ?? '';
        $intDigits = preg_replace('/[^\d]/', '', $intPart) ?? '0';
        $normalized = $fracPart !== '' ? $intDigits . '.' . $fracPart : $intDigits;
        $amount = (float) $normalized;

        return is_finite($amount) ? $amount : 0.0;
    }
}
