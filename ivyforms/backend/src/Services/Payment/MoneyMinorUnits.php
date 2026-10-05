<?php

namespace IvyForms\Services\Payment;

/**
 * Currency-aware conversion between major units and integer minor units.
 *
 * Do not use for parsing form money strings — that is MoneyParser's job.
 */
final class MoneyMinorUnits
{
    /**
     * ISO currencies that use zero decimal places (amount already in minor units as whole numbers).
     *
     * @see https://docs.stripe.com/currencies#zero-decimal
     *
     * @var list<string>
     */
    private const ZERO_DECIMAL = [
        'BIF', 'CLP', 'DJF', 'GNF', 'JPY', 'KMF', 'KRW', 'MGA',
        'PYG', 'RWF', 'UGX', 'VND', 'VUV', 'XAF', 'XOF', 'XPF',
    ];

    /**
     * PayPal rejects decimal `value` strings for these codes (JPY already in ZERO_DECIMAL).
     *
     * @see https://developer.paypal.com/api/rest/reference/currency-codes/
     *
     * @var list<string>
     */
    private const PAYPAL_ZERO_DECIMAL = ['HUF', 'TWD'];

    /**
     * Convert a major-unit amount to the integer minor units for the currency.
     */
    public static function toMinor(float $amount, string $currency): int
    {
        $code = strtoupper(trim($currency));

        if (in_array($code, self::ZERO_DECIMAL, true)) {
            return (int) round($amount);
        }

        return (int) round($amount * 100);
    }

    /**
     * Convert integer minor units back to a major-unit float for persistence/display.
     */
    public static function fromMinor(int $minor, string $currency): float
    {
        $code = strtoupper(trim($currency));

        if (in_array($code, self::ZERO_DECIMAL, true)) {
            return (float) $minor;
        }

        return $minor / 100;
    }

    /**
     * Format a major-unit amount for PayPal Orders (`value` string).
     */
    public static function formatMajor(float $amount, string $currency): string
    {
        $code = strtoupper(trim($currency));

        if (
            in_array($code, self::ZERO_DECIMAL, true)
            || in_array($code, self::PAYPAL_ZERO_DECIMAL, true)
        ) {
            return (string) (int) round($amount);
        }

        return number_format($amount, 2, '.', '');
    }

    /**
     * Lowest charge we will send to a processor (minor units).
     *
     * Matches the common card-network floor (~0.50 in two-decimal currencies)
     * so $0.01 card-testing charges are rejected before they hit the gateway.
     */
    public static function minimumChargeMinor(string $currency): int
    {
        unset($currency);

        return 50;
    }

    public static function isChargeable(float $amount, string $currency): bool
    {
        return self::toMinor($amount, $currency) >= self::minimumChargeMinor($currency);
    }
}
