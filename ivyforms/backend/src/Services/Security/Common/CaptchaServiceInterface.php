<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

namespace IvyForms\Services\Security\Common;

// phpcs:disable PSR1.Files.SideEffects
use IvyForms\Common\Exceptions\ForbiddenException;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Interface for CAPTCHA service implementations
 *
 * This interface defines the contract that all CAPTCHA services must implement,
 * providing a polymorphic approach for different CAPTCHA providers.
 *
 * @package IvyForms\Services\Security
 */
interface CaptchaServiceInterface
{
    /**
     * Verify CAPTCHA response with the provider
     *
     * @param string $response The CAPTCHA response token
     * @param string $secretKey The secret key
     * @param string $type The CAPTCHA type
     * @param string $userIp Optional user IP address
     * @return bool
     */
    public function verify(string $response, string $secretKey, string $type = 'v2', string $userIp = ''): bool;

    /**
     * Get the script URL for frontend integration
     *
     * @param string $type The CAPTCHA type
     * @param string $siteKey The site key
     * @param string $language Optional language code (default: 'en')
     * @return string
     */
    public function getScriptUrl(string $type, string $siteKey, string $language = 'en'): string;

    /**
     * Get frontend configuration for the CAPTCHA
     *
     * @param string $type The CAPTCHA type
     * @param string $siteKey The site key
     * @return array<string, mixed>
     */
    public function getFrontendConfig(string $type, string $siteKey): array;

    /**
     * Validate CAPTCHA credentials
     *
     * @param string $siteKey The site key
     * @param string $secretKey The secret key
     * @return array{success: bool, message: string}
     */
    public function validateCredentials(string $siteKey, string $secretKey): array;

    /**
     * Check if the CAPTCHA service is configured
     *
     * @return bool
     */
    public function isConfigured(): bool;

    /**
     * Get the site key from settings
     *
     * @return string
     */
    public function getSiteKey(): string;

    /**
     * Get the CAPTCHA type from settings
     *
     * @return string
     */
    public function getType(): string;

    /**
     * Validate form submission with CAPTCHA
     *
     * @param array<string, mixed> $submissionData The form submission data
     * @param array<object> $formFields The form field objects
     * @return bool
     * @throws ForbiddenException
     */
    public function validateFormSubmission(array $submissionData, array $formFields): bool;

    /**
     * Validate settings for this CAPTCHA provider
     *
     * @param array<string, mixed> $settings The settings to validate
     * @return array{success: bool, message: string}
     */
    public function validateSettings(array $settings): array;
}
