<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace IvyForms\Common\Constants;

/**
 * ISO 4217 codes supported for form payment display (keep in sync with frontend `supportedCurrencies.ts`).
 */
final class SupportedCurrencyCodes
{
    public const DEFAULT_CODE = 'USD';

    /**
     * @var string[]
     */
    public const CODES = [
        'USD',
        'EUR',
        'GBP',
        'CAD',
        'AUD',
        'JPY',
        'CHF',
        'SEK',
        'NOK',
        'DKK',
        'PLN',
        'CZK',
        'HUF',
        'NZD',
        'SGD',
        'HKD',
        'INR',
        'BRL',
        'MXN',
        'ZAR',
    ];

    /**
     * @param string $code
     *
     * @return bool
     */
    public static function isAllowed(string $code): bool
    {
        return in_array($code, self::CODES, true);
    }
}
