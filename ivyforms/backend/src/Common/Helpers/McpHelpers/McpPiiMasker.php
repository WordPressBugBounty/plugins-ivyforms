<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

namespace IvyForms\Common\Helpers\McpHelpers;

/**
 * Email and phone masking helpers for MCP response redaction.
 */
class McpPiiMasker
{
    public const REDACTED_PLACEHOLDER = '***redacted***';

    public static function maskString(string $value): string
    {
        if (self::looksLikeEmail($value)) {
            return self::maskEmail($value);
        }

        if (self::looksLikePhone($value)) {
            return self::maskPhone($value);
        }

        return $value;
    }

    private static function looksLikeEmail(string $value): bool
    {
        return strpos($value, '@') !== false && is_email($value) !== false;
    }

    private static function maskEmail(string $email): string
    {
        $atPos = strrpos($email, '@');
        if ($atPos === false || $atPos === 0) {
            return self::REDACTED_PLACEHOLDER;
        }

        $local  = substr($email, 0, $atPos);
        $domain = substr($email, $atPos + 1);

        return $local[0] . str_repeat('*', max(1, strlen($local) - 1)) . '@' . $domain;
    }

    private static function looksLikePhone(string $value): bool
    {
        $stripped = preg_replace('/[^0-9]/', '', $value);
        if (!is_string($stripped)) {
            return false;
        }

        $digitCount = strlen($stripped);

        return $digitCount >= 7 && $digitCount <= 15
            && (bool) preg_match('/^[\+\(\)\-\s\d]+$/', $value);
    }

    private static function maskPhone(string $value): string
    {
        $digits = preg_replace('/[^0-9]/', '', $value);
        if (!is_string($digits) || $digits === '') {
            return self::REDACTED_PLACEHOLDER;
        }

        $last = substr($digits, -2);

        return '***' . $last;
    }
}
