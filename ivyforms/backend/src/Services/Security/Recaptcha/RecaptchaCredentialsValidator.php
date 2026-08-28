<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

namespace IvyForms\Services\Security\Recaptcha;

use IvyForms\Services\Translations\BackendStrings;

/**
 * Handles reCAPTCHA credentials validation
 *
 * Extracted from RecaptchaService to reduce complexity
 *
 * @package IvyForms\Services\Security
 */
class RecaptchaCredentialsValidator
{
    /**
     * Validate reCAPTCHA key format
     *
     * @param string $key
     * @return bool
     */
    public static function validateKeyFormat(string $key): bool
    {
        if (empty($key)) {
            return false;
        }

        // reCAPTCHA keys are typically 40 characters long
        if (strlen($key) !== 40) {
            return false;
        }

        // Basic format validation - should contain alphanumeric characters and some special chars
        if (!preg_match('/^[A-Za-z0-9_-]+$/', $key)) {
            return false;
        }

        // reCAPTCHA site and secret keys typically start with specific prefixes
        $validPrefixes = ['6L', '6I']; // Common reCAPTCHA key prefixes

        foreach ($validPrefixes as $prefix) {
            if (strpos($key, $prefix) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Validate both site key and secret key formats
     *
     * @param string $siteKey
     * @param string $secretKey
     * @return array{success: bool, message: string}
     */
    public static function validateKeyFormats(string $siteKey, string $secretKey): array
    {
        $strings = BackendStrings::getSecurityStrings();

        if (!self::validateKeyFormat($siteKey)) {
            return [
                'success' => false,
                'message' => $strings['recaptcha_invalid_site_key']
            ];
        }

        if (!self::validateKeyFormat($secretKey)) {
            return [
                'success' => false,
                'message' => $strings['recaptcha_invalid_key_format']
            ];
        }

        return ['success' => true, 'message' => ''];
    }

    /**
     * Process Google API verification result
     *
     * @param array<string, mixed>|false $verificationResult
     * @return array{success: bool, message: string}
     */
    public static function processVerificationResult($verificationResult): array
    {
        $strings = BackendStrings::getSecurityStrings();

        if ($verificationResult === false) {
            return [
                'success' => false,
                'message' => $strings['recaptcha_validation_network_error']
            ];
        }

        return self::checkErrorCodes($verificationResult, $strings);
    }

    /**
     * Check for specific error codes in verification result
     *
     * @param array<string, mixed> $verificationResult
     * @param array<string, string> $strings
     * @return array{success: bool, message: string}
     */
    private static function checkErrorCodes(array $verificationResult, array $strings): array
    {
        if (!isset($verificationResult['error-codes'])) {
            return self::handleSuccessCase($verificationResult, $strings);
        }

        $errorCodes = $verificationResult['error-codes'];

        if (in_array('invalid-input-secret', $errorCodes)) {
            return [
                'success' => false,
                'message' => $strings['recaptcha_invalid_secret_key']
            ];
        }

        if (in_array('missing-input-secret', $errorCodes)) {
            return [
                'success' => false,
                'message' => $strings['recaptcha_keys_required']
            ];
        }

        // If we get 'invalid-input-response' or 'missing-input-response',
        // it means the secret key is valid but our test token is invalid
        if (self::hasValidSecretKeyErrors($errorCodes)) {
            return [
                'success' => true,
                'message' => $strings['recaptcha_credentials_valid']
            ];
        }

        return self::handleSuccessCase($verificationResult, $strings);
    }

    /**
     * Check if error codes indicate valid secret key
     *
     * @param array<string> $errorCodes
     * @return bool
     */
    private static function hasValidSecretKeyErrors(array $errorCodes): bool
    {
        return in_array('invalid-input-response', $errorCodes) ||
               in_array('missing-input-response', $errorCodes);
    }

    /**
     * Handle the success case of verification
     *
     * @param array<string, mixed> $verificationResult
     * @param array<string, string> $strings
     * @return array{success: bool, message: string}
     */
    private static function handleSuccessCase(array $verificationResult, array $strings): array
    {
        if (!$verificationResult['success']) {
            return [
                'success' => false,
                'message' => $strings['recaptcha_unable_to_validate']
            ];
        }

        return [
            'success' => true,
            'message' => $strings['recaptcha_credentials_valid']
        ];
    }
}
