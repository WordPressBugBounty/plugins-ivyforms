<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

namespace IvyForms\Services\Security\Recaptcha;

use IvyForms\Common\Exceptions\ForbiddenException;
use IvyForms\Services\Security\Common\CaptchaServiceInterface;
use IvyForms\Services\Security\Common\IpDetectionService;
use IvyForms\Services\Settings\SettingsService;
use IvyForms\Services\Translations\BackendStrings;

/**
 * Class RecaptchaService
 *
 * @package IvyForms\Services\Security
 */
class RecaptchaService implements CaptchaServiceInterface
{
    private const GOOGLE_RECAPTCHA_VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';
    private const GOOGLE_RECAPTCHA_SCRIPT_URL = 'https://www.google.com/recaptcha/api.js';
    private const MIN_SCORE_V3 = 0.5; // Minimum score for reCAPTCHA v3

    private SettingsService $settingsService;

    public function __construct(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
    }

    /**
     * Verify reCAPTCHA response with Google
     *
     * @param string $response The reCAPTCHA response token
     * @param string $secretKey The secret key
     * @param string $type The reCAPTCHA type (v2, v3, invisible)
     * @param string $userIp Optional user IP address
     * @return bool
     */
    public function verify(string $response, string $secretKey, string $type = 'v2', string $userIp = ''): bool
    {
        if (empty($response) || empty($secretKey)) {
            return false;
        }

        $verificationResult = $this->callGoogleApi($response, $secretKey, $userIp);

        if (!$verificationResult) {
            return false;
        }

        // For reCAPTCHA v3, also check the score
        if ($type === 'v3') {
            return $this->validateV3Score($verificationResult);
        }

        return $verificationResult['success'] === true;
    }

    /**
     * Call Google reCAPTCHA API for verification
     *
     * @param string $response
     * @param string $secretKey
     * @param string $userIp
     * @return array<string, mixed>|false
     */
    private function callGoogleApi(string $response, string $secretKey, string $userIp = '')
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

        $response = wp_remote_post(self::GOOGLE_RECAPTCHA_VERIFY_URL, $args);

        if (is_wp_error($response)) {
            $exceptionStrings = BackendStrings::getExceptionStrings();
            error_log(sprintf($exceptionStrings['recaptcha_error_verification'], $response->get_error_message()));
            return false;
        }

        $responseCode = wp_remote_retrieve_response_code($response);
        if ($responseCode !== 200) {
            $exceptionStrings = BackendStrings::getExceptionStrings();
            error_log(sprintf($exceptionStrings['recaptcha_error_http_code'], $responseCode));
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $result = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $exceptionStrings = BackendStrings::getExceptionStrings();
            error_log($exceptionStrings['recaptcha_error_invalid_json']);
            return false;
        }

        return $result;
    }

    /**
     * Validate reCAPTCHA v3 score
     *
     * @param array<string, mixed> $verificationResult
     * @return bool
     */
    private function validateV3Score(array $verificationResult): bool
    {
        $isSuccess = $verificationResult['success'] ?? false;
        $score = (float) ($verificationResult['score'] ?? 0);
        $minScore = apply_filters('ivyforms_recaptcha_v3_min_score', self::MIN_SCORE_V3);

        return $isSuccess === true && $score >= $minScore;
    }

    /**
     * Get the appropriate reCAPTCHA script URL based on type
     *
     * @param string $type The reCAPTCHA type (v2, v3, invisible)
     * @param string $siteKey The site key
     * @param string $language Optional language code (default: 'en')
     * @return string The complete script URL
     */
    public function getScriptUrl(string $type, string $siteKey, string $language = 'en'): string
    {
        $baseUrl = self::GOOGLE_RECAPTCHA_SCRIPT_URL;

        if ($type === 'v3') {
            return $baseUrl . '?render=' . $siteKey . "&hl=" . $language;
        }

        $url = $baseUrl . '?render=explicit&hl=' . $language;
        return !empty($siteKey) ? $url . '&onload=onRecaptchaLoad' : $url;
    }

    /**
     * Get reCAPTCHA configuration for frontend
     *
     * @param string $type
     * @param string $siteKey
     * @return array<string, mixed>
     */
    public function getFrontendConfig(string $type, string $siteKey): array
    {
        $settings = $this->getSettings();
        $language = $settings['language'] ?? 'en';

        return [
            'type'      => $type,
            'siteKey'   => $siteKey,
            'scriptUrl' => $this->getScriptUrl($type, $siteKey, $language),
            'size'      => $type === 'invisible' ? 'invisible' : 'normal',
        ];
    }

    /**
     * Validate form submission with reCAPTCHA
     *
     * @param array<string, mixed> $submissionData Raw form submission data
     * @param array<object> $formFields Array of form field objects
     * @return bool
     * @throws ForbiddenException If reCAPTCHA validation fails
     */
    public function validateFormSubmission(array $submissionData, array $formFields): bool
    {
        // Find reCAPTCHA field in the form
        $recaptchaFieldInfo = $this->findRecaptchaField($formFields);

        // If no reCAPTCHA field, skip validation
        if (!$recaptchaFieldInfo['hasField']) {
            return true;
        }

        // Check if reCAPTCHA is configured
        if (!$this->isConfigured()) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                $exceptionStrings = BackendStrings::getExceptionStrings();
                error_log($exceptionStrings['recaptcha_error_not_configured']);
            }
            return true;
        }

        // Get and validate reCAPTCHA token
        $recaptchaToken = $this->extractRecaptchaToken($submissionData, $recaptchaFieldInfo['fieldKey']);

        // Perform validation
        return $this->performTokenValidation($recaptchaToken);
    }

    /**
     * Find reCAPTCHA field in form fields
     *
     * @param array<object> $formFields
     * @return array{hasField: bool, fieldKey: string}
     */
    private function findRecaptchaField(array $formFields): array
    {
        foreach ($formFields as $field) {
            if ($field->getType() === 'recaptcha') {
                return [
                    'hasField' => true,
                    'fieldKey' => 'recaptcha_' . $field->getIndex()
                ];
            }
        }

        return ['hasField' => false, 'fieldKey' => ''];
    }

    /**
     * Extract reCAPTCHA token from submission data
     *
     * @param array<string, mixed> $submissionData
     * @param string $fieldKey
     * @return string
     * @throws ForbiddenException If token is missing
     */
    private function extractRecaptchaToken(array $submissionData, string $fieldKey): string
    {
        $recaptchaToken = $submissionData['values'][$fieldKey] ?? '';

        if (empty($recaptchaToken)) {
            $strings = BackendStrings::getSecurityStrings();
            throw new ForbiddenException($strings['recaptcha_verification_required']);
        }

        return $recaptchaToken;
    }

    /**
     * Perform token validation with Google API
     *
     * @param string $recaptchaToken
     * @return bool
     * @throws ForbiddenException If validation fails
     */
    private function performTokenValidation(string $recaptchaToken): bool
    {
        $userIp = IpDetectionService::getUserIpAddress();
        $isValid = $this->validateResponse($recaptchaToken, $userIp);

        if ($isValid) {
            return true;
        }

        $this->logValidationFailure($recaptchaToken);
        $strings = BackendStrings::getSecurityStrings();
        throw new ForbiddenException($strings['recaptcha_verification_failed']);
    }

    /**
     * Log validation failure in debug mode
     *
     * @param string $recaptchaToken
     * @return void
     */
    private function logValidationFailure(string $recaptchaToken): void
    {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $exceptionStrings = BackendStrings::getExceptionStrings();
            error_log(
                sprintf(
                    $exceptionStrings['recaptcha_error_validation_failed'],
                    substr($recaptchaToken, 0, 20) . '...'
                )
            );
        }
    }
    /**
     * Validate reCAPTCHA key format
     *
     * @param string $key
     * @return bool
     */
    public static function validateKeyFormat(string $key): bool
    {
        return RecaptchaCredentialsValidator::validateKeyFormat($key);
    }

    /**
     * Validate reCAPTCHA credentials by testing with Google API
     *
     * @param string $siteKey
     * @param string $secretKey
     * @return array{success: bool, message: string}
     */
    public function validateCredentials(string $siteKey, string $secretKey): array
    {
        // Validate key formats first
        $formatValidation = RecaptchaCredentialsValidator::validateKeyFormats($siteKey, $secretKey);
        if (!$formatValidation['success']) {
            return $formatValidation;
        }

        // Test keys with Google API using dummy verification
        $testResponse = 'test-validation-token-' . uniqid();
        $verificationResult = $this->callGoogleApi($testResponse, $secretKey);

        return RecaptchaCredentialsValidator::processVerificationResult($verificationResult);
    }

    /**
     * Get reCAPTCHA settings
     *
     * @return array<string, mixed>
     */
    public function getSettings(): array
    {
        $settings = $this->settingsService->getSetting('security', 'recaptcha');

        return [
            'type'      => $settings['type'] ?? 'v2',
            'siteKey'   => $settings['siteKey'] ?? '',
            'secretKey' => $settings['secretKey'] ?? '',
            'language'  => $settings['language'] ?? '',
        ];
    }

    /**
     * Check if reCAPTCHA is configured
     *
     * @return bool
     */
    public function isConfigured(): bool
    {
        $settings = $this->getSettings();
        return !empty($settings['siteKey']) && !empty($settings['secretKey']);
    }

    /**
     * Get reCAPTCHA site key for frontend
     *
     * @return string
     */
    public function getSiteKey(): string
    {
        $settings = $this->getSettings();
        return $settings['siteKey'] ?? '';
    }

    /**
     * Get reCAPTCHA type
     *
     * @return string
     */
    public function getType(): string
    {
        $settings = $this->getSettings();
        return $settings['type'] ?? 'v2';
    }

    /**
     * Validate reCAPTCHA response (main validation method)
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
            $settings['type'],
            $userIp
        );
    }

    /**
     * Validate settings for this CAPTCHA provider
     *
     * @param array<string, mixed> $settings The settings to validate
     * @return array{success: bool, message: string}
     */
    public function validateSettings(array $settings): array
    {
        $siteKey = $settings['siteKey'] ?? '';
        $secretKey = $settings['secretKey'] ?? '';

        if (empty($siteKey) || empty($secretKey)) {
            $strings = BackendStrings::getSecurityStrings();
            return [
                'success' => false,
                'message' => $strings['recaptcha_keys_required']
            ];
        }

        return $this->validateCredentials($siteKey, $secretKey);
    }
}
