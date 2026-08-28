<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

namespace IvyForms\Services\Security\HCaptcha;

use IvyForms\Common\Exceptions\ForbiddenException;
use IvyForms\Services\Security\Common\CaptchaServiceInterface;
use IvyForms\Services\Security\Common\IpDetectionService;
use IvyForms\Services\Settings\SettingsService;
use IvyForms\Services\Translations\BackendStrings;

/**
 * Class HCaptchaService
 *
 * @package IvyForms\Services\Security
 */
class HCaptchaService implements CaptchaServiceInterface
{
    private const HCAPTCHA_VERIFY_URL = 'https://hcaptcha.com/siteverify';
    private const HCAPTCHA_SCRIPT_URL = 'https://js.hcaptcha.com/1/api.js';

    private SettingsService $settingsService;

    public function __construct(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
    }

    /**
     * Verify hCaptcha response with hCaptcha API
     *
     * @param string $response The hCaptcha response token
     * @param string $secretKey The secret key
     * @param string $type The hCaptcha type (checkbox, invisible)
     * @param string $userIp Optional user IP address
     * @return bool
     */
    public function verify(string $response, string $secretKey, string $type = 'checkbox', string $userIp = ''): bool
    {
        if (empty($response) || empty($secretKey)) {
            return false;
        }

        $verificationResult = $this->callHCaptchaApi($response, $secretKey, $userIp);

        if (!$verificationResult) {
            return false;
        }

        return $verificationResult['success'] === true;
    }

    /**
     * Call hCaptcha API for verification
     *
     * @param string $response
     * @param string $secretKey
     * @param string $userIp
     * @return array<string, mixed>|false
     */
    private function callHCaptchaApi(string $response, string $secretKey, string $userIp = '')
    {
        $settings = $this->getSettings();
        $siteKey = $settings['siteKey'] ?? '';

        $postData = [
            'secret' => $secretKey,
            'response' => $response,
        ];

        if (!empty($userIp)) {
            $postData['remoteip'] = $userIp;
        }

        if (!empty($siteKey)) {
            $postData['sitekey'] = $siteKey;
        }

        $args = [
            'body' => $postData,
            'method' => 'POST',
            'timeout' => 15,
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
        ];

        $response = wp_remote_post(self::HCAPTCHA_VERIFY_URL, $args);

        if (is_wp_error($response)) {
            error_log(sprintf(
                BackendStrings::getExceptionStrings()['hcaptcha_error_verification'],
                $response->get_error_message()
            ));
            return false;
        }

        $responseCode = wp_remote_retrieve_response_code($response);
        if ($responseCode !== 200) {
            error_log(sprintf(
                BackendStrings::getExceptionStrings()['hcaptcha_error_http_code'],
                $responseCode
            ));
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $result = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log(BackendStrings::getExceptionStrings()['hcaptcha_error_invalid_json']);
            return false;
        }

        return $result;
    }

    /**
     * Get the appropriate hCaptcha script URL
     *
     * @param string $type The hCaptcha type (checkbox, invisible)
     * @param string $siteKey The site key
     * @return string The complete script URL
     */
    public function getScriptUrl(string $type, string $siteKey, string $language = 'auto'): string
    {
        return self::HCAPTCHA_SCRIPT_URL . '?render=explicit&onload=onHCaptchaLoad';
    }

    /**
     * Get hCaptcha configuration for frontend
     *
     * @param string $type
     * @param string $siteKey
     * @return array<string, mixed>
     */
    public function getFrontendConfig(string $type, string $siteKey): array
    {
        return [
            'type'      => $type,
            'siteKey'   => $siteKey,
            'scriptUrl' => $this->getScriptUrl($type, $siteKey),
        ];
    }

    /**
     * Validate hCaptcha credentials with hCaptcha API
     *
     * @param string $siteKey The site key to validate
     * @param string $secretKey The secret key to validate
     * @return array{success: bool, message: string}
     */
    public function validateCredentials(string $siteKey, string $secretKey): array
    {
        // Basic format validation first
        if (empty($siteKey) || empty($secretKey)) {
            return [
                'success' => false,
                'message' => BackendStrings::getExceptionStrings()['hcaptcha_keys_incomplete'],
            ];
        }

        // hCaptcha keys validation - check format
        if (!$this->isValidKeyFormat($siteKey, 'site') || !$this->isValidKeyFormat($secretKey, 'secret')) {
            return [
                'success' => false,
                'message' => BackendStrings::getExceptionStrings()['hcaptcha_invalid_key_format'],
            ];
        }

        // Note: Unlike reCAPTCHA, hCaptcha doesn't provide a simple credentials validation endpoint
        // We can only verify the format here. Actual validation happens during form submission.
        return [
            'success' => true,
            'message' => BackendStrings::getExceptionStrings()['hcaptcha_keys_valid'],
        ];
    }

    /**
     * Validate hCaptcha key format
     *
     * hCaptcha keys typically follow these formats:
     * - Site keys: UUID-like format (e.g., 10000000-ffff-ffff-ffff-000000000001)
     * - Secret keys: "0x" followed by 38-40 hex characters (40-42 chars total)
     *
     * @param string $key
     * @param string $keyType Either 'site' or 'secret'
     * @return bool
     */
    private function isValidKeyFormat(string $key, string $keyType = 'site'): bool
    {
        if ($keyType === 'site') {
            // Site keys are UUID-like: 8-4-4-4-12 hex characters separated by hyphens
            $pattern = '/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-'
                . '[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/';
            return preg_match($pattern, $key) === 1;
        }

        // Secret keys start with "0x" followed by hex characters (40-42 chars total)
        return preg_match('/^0x[0-9a-fA-F]{38,40}$/', $key) === 1;
    }

    /**
     * Check if hCaptcha is configured
     *
     * @return bool
     */
    public function isConfigured(): bool
    {
        $settings = $this->getSettings();

        return !empty($settings['siteKey']) && !empty($settings['secretKey']);
    }

    /**
     * Get site key from settings
     *
     * @return string
     */
    public function getSiteKey(): string
    {
        $settings = $this->getSettings();
        return $settings['siteKey'] ?? '';
    }

    /**
     * Get hCaptcha type from settings
     *
     * @return string
     */
    public function getType(): string
    {
        $settings = $this->getSettings();
        return $settings['type'] ?? 'checkbox';
    }

    /**
     * Get hCaptcha settings
     *
     * @return array<string, mixed>
     */
    public function getSettings(): array
    {
        try {
            return $this->settingsService->getSetting('security', 'hcaptcha') ?? [];
        } catch (\Exception $e) {
            error_log(sprintf(
                BackendStrings::getExceptionStrings()['hcaptcha_error_loading_settings'],
                $e->getMessage()
            ));
            return [];
        }
    }

    /**
     * Validate form submission with hCaptcha
     *
     * @param array<string, mixed> $submissionData The form submission data
     * @param array<object> $formFields The form field objects
     * @return bool
     * @throws ForbiddenException
     */
    public function validateFormSubmission(array $submissionData, array $formFields): bool
    {
        // Find hCaptcha field in the form
        $hcaptchaFieldInfo = $this->findHCaptchaField($formFields);

        // If no hCaptcha field, skip validation
        if (!$hcaptchaFieldInfo['hasField']) {
            return true;
        }

        // Check if hCaptcha is configured
        if (!$this->isConfigured()) {
            throw new ForbiddenException(
                BackendStrings::getExceptionStrings()['hcaptcha_not_configured']
            );
        }

        // Get and validate hCaptcha token
        $hcaptchaToken = $this->extractHCaptchaToken($submissionData, $hcaptchaFieldInfo['fieldKey']);

        // Perform validation
        return $this->performTokenValidation($hcaptchaToken);
    }

    /**
     * Find hCaptcha field in form fields
     *
     * @param array<object> $formFields
     * @return array{hasField: bool, fieldKey: string}
     */
    private function findHCaptchaField(array $formFields): array
    {
        foreach ($formFields as $field) {
            if ($field->getType() === 'hcaptcha') {
                return [
                    'hasField' => true,
                    'fieldKey' => 'hcaptcha_' . $field->getIndex()
                ];
            }
        }

        return ['hasField' => false, 'fieldKey' => ''];
    }

    /**
     * Extract hCaptcha token from submission data
     *
     * @param array<string, mixed> $submissionData
     * @param string $fieldKey
     * @return string
     * @throws ForbiddenException If token is missing
     */
    private function extractHCaptchaToken(array $submissionData, string $fieldKey): string
    {
        $hcaptchaToken = $submissionData['values'][$fieldKey] ?? '';

        if (empty($hcaptchaToken)) {
            throw new ForbiddenException(
                BackendStrings::getExceptionStrings()['hcaptcha_response_required']
            );
        }

        return $hcaptchaToken;
    }

    /**
     * Perform token validation with hCaptcha API
     *
     * @param string $hcaptchaToken
     * @return bool
     * @throws ForbiddenException If validation fails
     */
    private function performTokenValidation(string $hcaptchaToken): bool
    {
        // Get settings
        $settings = $this->getSettings();
        $secretKey = $settings['secretKey'] ?? '';
        $type = $settings['type'] ?? 'checkbox';

        // Get user IP
        $userIp = IpDetectionService::getUserIpAddress();

        // Verify with hCaptcha
        $isValid = $this->verify($hcaptchaToken, $secretKey, $type, $userIp);

        if (!$isValid) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log(sprintf(
                    BackendStrings::getExceptionStrings()['hcaptcha_error_validation_failed'],
                    substr($hcaptchaToken, 0, 20) . '...'
                ));
            }
            throw new ForbiddenException(
                BackendStrings::getExceptionStrings()['hcaptcha_verification_failed']
            );
        }

        return true;
    }

    /**
     * Validate hCaptcha response (main validation method)
     *
     * @param string $response
     * @param string $userIp
     * @return bool
     */
    public function validateResponse(string $response, string $userIp = ''): bool
    {
        if (!$this->isConfigured()) {
            return true; // If not configured, pass validation
        }

        $settings = $this->getSettings();

        return $this->verify(
            $response,
            $settings['secretKey'],
            $settings['type'] ?? 'checkbox',
            $userIp
        );
    }

    /**
     * Validate settings for hCaptcha
     *
     * @param array<string, mixed> $settings The settings to validate
     * @return array{success: bool, message: string}
     */
    public function validateSettings(array $settings): array
    {
        // Validate required fields
        if (!isset($settings['siteKey']) || !isset($settings['secretKey'])) {
            return [
                'success' => false,
                'message' => BackendStrings::getExceptionStrings()['hcaptcha_keys_incomplete'],
            ];
        }

        // Validate credentials
        return $this->validateCredentials($settings['siteKey'], $settings['secretKey']);
    }
}
