<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

namespace IvyForms\Services\Security\Turnstile;

use IvyForms\Common\Exceptions\ForbiddenException;
use IvyForms\Services\Security\Common\CaptchaServiceInterface;
use IvyForms\Services\Security\Common\IpDetectionService;
use IvyForms\Services\Settings\SettingsService;
use IvyForms\Services\Translations\BackendStrings;

/**
 * Class TurnstileService
 *
 * @package IvyForms\Services\Security
 */
class TurnstileService implements CaptchaServiceInterface
{
    private const CLOUDFLARE_TURNSTILE_VERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
    private const CLOUDFLARE_TURNSTILE_SCRIPT_URL = 'https://challenges.cloudflare.com/turnstile/v0/api.js';

    private SettingsService $settingsService;

    public function __construct(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
    }

    /**
     * Verify Turnstile response with Cloudflare
     *
     * @param string $response The Turnstile response token
     * @param string $secretKey The secret key
     * @param string $type The Turnstile type (deprecated, kept for compatibility)
     * @param string $userIp Optional user IP address
     * @return bool
     */
    public function verify(string $response, string $secretKey, string $type = '', string $userIp = ''): bool
    {
        if (empty($response) || empty($secretKey)) {
            return false;
        }

        $verificationResult = $this->callCloudflareApi($response, $secretKey, $userIp);

        if (!$verificationResult) {
            return false;
        }

        return $verificationResult['success'] === true;
    }

    /**
     * Call Cloudflare Turnstile API for verification
     *
     * @param string $response
     * @param string $secretKey
     * @param string $userIp
     * @return array<string, mixed>|false
     */
    private function callCloudflareApi(string $response, string $secretKey, string $userIp = '')
    {
        $postData = [
            'secret' => $secretKey,
            'response' => $response,
        ];

        if (!empty($userIp)) {
            $postData['remoteip'] = $userIp;
        }

        $args = [
            'body' => $postData,
            'method' => 'POST',
            'timeout' => 15,
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
        ];

        $response = wp_remote_post(self::CLOUDFLARE_TURNSTILE_VERIFY_URL, $args);

        if (is_wp_error($response)) {
            error_log(sprintf(
                BackendStrings::getExceptionStrings()['turnstile_error_verification'],
                $response->get_error_message()
            ));
            return false;
        }

        $responseCode = wp_remote_retrieve_response_code($response);
        if ($responseCode !== 200) {
            error_log(sprintf(
                BackendStrings::getExceptionStrings()['turnstile_error_http_code'],
                $responseCode
            ));
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $result = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log(BackendStrings::getExceptionStrings()['turnstile_error_invalid_json']);
            return false;
        }

        return $result;
    }

    /**
     * Get the appropriate Turnstile script URL
     *
     * @param string $type The Turnstile type (deprecated, kept for compatibility)
     * @param string $siteKey The site key
     * @param string $language Optional language code (default: 'auto')
     * @return string The complete script URL
     */
    public function getScriptUrl(string $type, string $siteKey, string $language = 'auto'): string
    {
        $url = self::CLOUDFLARE_TURNSTILE_SCRIPT_URL . '?render=explicit';

        if (!empty($language) && $language !== 'auto') {
            $url .= '&hl=' . $language;
        }

        return $url;
    }

    /**
     * Get Turnstile configuration for frontend
     *
     * @param string $type
     * @param string $siteKey
     * @return array<string, mixed>
     */
    public function getFrontendConfig(string $type, string $siteKey): array
    {
        $settings = $this->getSettings();
        $theme = $settings['theme'] ?? 'auto';
        $language = $settings['language'] ?? 'auto';

        return [
            'siteKey'   => $siteKey,
            'scriptUrl' => $this->getScriptUrl($type, $siteKey, $language),
            'theme'     => $theme,
            'language'  => $language,
            'size'      => 'normal',
        ];
    }

    /**
     * Validate Turnstile credentials with Cloudflare
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
                'message' => BackendStrings::getExceptionStrings()['turnstile_keys_incomplete'],
            ];
        }

        // Turnstile keys validation - check format
        if (!$this->isValidKeyFormat($siteKey) || !$this->isValidKeyFormat($secretKey)) {
            return [
                'success' => false,
                'message' => BackendStrings::getExceptionStrings()['turnstile_invalid_key_format'],
            ];
        }

        // Note: Unlike reCAPTCHA, Cloudflare Turnstile doesn't provide a simple credentials validation endpoint
        // We can only verify the format here. Actual validation happens during form submission.
        return [
            'success' => true,
            'message' => BackendStrings::getExceptionStrings()['turnstile_keys_valid'],
        ];
    }

    /**
     * Validate Turnstile key format
     *
     * Cloudflare Turnstile keys typically follow these formats:
     * - Production keys: "0x" followed by 38 hex characters (40 chars total)
     * - Test site keys: "1x", "2x", or "3x" followed by hex chars (26 chars total)
     * - Test secret keys: "1x", "2x", or "3x" followed by hex chars (35 chars total)
     *
     * @param string $key
     * @return bool
     */
    private function isValidKeyFormat(string $key): bool
    {
        // Accept production keys (0x + 38 hex chars = 40 total)
        // or test keys (1x/2x/3x + varying hex chars, typically 26 or 35 chars)
        return preg_match('/^[0-3]x[0-9a-fA-F]{24,38}$/', $key) === 1;
    }

    /**
     * Check if Turnstile is configured
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
     * Get Turnstile type from settings
     * Note: Turnstile type modes have been removed, this returns empty string for compatibility
     *
     * @return string
     */
    public function getType(): string
    {
        return '';
    }

    /**
     * Get Turnstile settings
     *
     * @return array<string, mixed>
     */
    private function getSettings(): array
    {
        try {
            return $this->settingsService->getSetting('security', 'turnstile') ?? [];
        } catch (\Exception $e) {
            error_log(sprintf(
                BackendStrings::getExceptionStrings()['turnstile_error_loading_settings'],
                $e->getMessage()
            ));
            return [];
        }
    }

    /**
     * Validate form submission with Turnstile
     *
     * @param array<string, mixed> $submissionData The form submission data
     * @param array<object> $formFields The form field objects
     * @return bool
     * @throws ForbiddenException
     */
    public function validateFormSubmission(array $submissionData, array $formFields): bool
    {
        // Find Turnstile field in the form
        $turnstileFieldInfo = $this->findTurnstileField($formFields);

        // If no Turnstile field, skip validation
        if (!$turnstileFieldInfo['hasField']) {
            return true;
        }

        // Check if Turnstile is configured
        if (!$this->isConfigured()) {
            throw new ForbiddenException(
                BackendStrings::getExceptionStrings()['turnstile_not_configured']
            );
        }

        // Get and validate Turnstile token
        $turnstileToken = $this->extractTurnstileToken($submissionData, $turnstileFieldInfo['fieldKey']);

        // Perform validation
        return $this->performTokenValidation($turnstileToken);
    }

    /**
     * Find Turnstile field in form fields
     *
     * @param array<object> $formFields
     * @return array{hasField: bool, fieldKey: string}
     */
    private function findTurnstileField(array $formFields): array
    {
        foreach ($formFields as $field) {
            if ($field->getType() === 'turnstile') {
                return [
                    'hasField' => true,
                    'fieldKey' => 'turnstile_' . $field->getIndex()
                ];
            }
        }

        return ['hasField' => false, 'fieldKey' => ''];
    }

    /**
     * Extract Turnstile token from submission data
     *
     * @param array<string, mixed> $submissionData
     * @param string $fieldKey
     * @return string
     * @throws ForbiddenException If token is missing
     */
    private function extractTurnstileToken(array $submissionData, string $fieldKey): string
    {
        $turnstileToken = $submissionData['values'][$fieldKey] ?? '';

        if (empty($turnstileToken)) {
            throw new ForbiddenException(
                BackendStrings::getExceptionStrings()['turnstile_response_required']
            );
        }

        return $turnstileToken;
    }

    /**
     * Perform token validation with Cloudflare API
     *
     * @param string $turnstileToken
     * @return bool
     * @throws ForbiddenException If validation fails
     */
    private function performTokenValidation(string $turnstileToken): bool
    {
        // Get settings
        $settings = $this->getSettings();
        $secretKey = $settings['secretKey'] ?? '';

        // Get user IP
        $userIp = IpDetectionService::getUserIpAddress();

        // Verify with Cloudflare
        $isValid = $this->verify($turnstileToken, $secretKey, '', $userIp);

        if (!$isValid) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log(sprintf(
                    BackendStrings::getExceptionStrings()['turnstile_error_validation_failed'],
                    substr($turnstileToken, 0, 20) . '...'
                ));
            }
            throw new ForbiddenException(
                BackendStrings::getExceptionStrings()['turnstile_verification_failed']
            );
        }

        return true;
    }

    /**
     * Validate settings for Turnstile
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
                'message' => BackendStrings::getExceptionStrings()['turnstile_keys_incomplete'],
            ];
        }

        // Validate credentials
        return $this->validateCredentials($settings['siteKey'], $settings['secretKey']);
    }
}
