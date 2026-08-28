<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

namespace IvyForms\Services\Security;

use IvyForms\Common\Exceptions\ForbiddenException;
use IvyForms\Factory\Security\SecurityServiceFactory;
use IvyForms\Factory\Security\CaptchaProviderResolver;
use IvyForms\Services\Security\Common\CaptchaServiceInterface;
use IvyForms\Services\Security\Recaptcha\RecaptchaConfigProvider;
use IvyForms\Services\Security\Turnstile\TurnstileConfigProvider;
use IvyForms\Services\Security\HCaptcha\HCaptchaConfigProvider;

/**
 * Class SecurityService
 *
 * Central service for all security-related functionality including CAPTCHA validation.
 * This service acts as a facade over the SecurityServiceFactory, providing a clean
 * interface for controllers to interact with security features.
 *
 * @package IvyForms\Services\Security
 */
class SecurityService
{
    /**
     * @var SecurityServiceFactory
     */
    private SecurityServiceFactory $securityServiceFactory;

    /**
     * @var RecaptchaConfigProvider
     */
    private RecaptchaConfigProvider $recaptchaConfigProvider;

    /**
     * @var TurnstileConfigProvider
     */
    private TurnstileConfigProvider $turnstileConfigProvider;

    /**
     * @var HCaptchaConfigProvider
     */
    private HCaptchaConfigProvider $hcaptchaConfigProvider;

    public function __construct(SecurityServiceFactory $securityServiceFactory)
    {
        $this->securityServiceFactory = $securityServiceFactory;
        $this->recaptchaConfigProvider = new RecaptchaConfigProvider();
        $this->turnstileConfigProvider = new TurnstileConfigProvider();
        $this->hcaptchaConfigProvider = new HCaptchaConfigProvider();
    }

    /**
     * Get the currently active CAPTCHA provider
     *
     * @return string ('recaptcha', 'hcaptcha', 'turnstile', 'none')
     */
    public function getActiveCaptchaProvider(): string
    {
        return $this->securityServiceFactory->getActiveCaptchaProvider();
    }

    /**
     * Determine CAPTCHA provider based on form fields and current settings
     *
     * @param array<object> $formFields Array of form field objects
     * @return string The CAPTCHA provider to use for this form
     */
    public function getCaptchaProviderForForm(array $formFields): string
    {
        return $this->securityServiceFactory->getCaptchaProviderForForm($formFields);
    }

    /**
     * Validate CAPTCHA response for form submission
     *
     * This method automatically determines the appropriate CAPTCHA provider
     * based on the form fields and validates the submission accordingly.
     *
     * @param array<string,mixed> $submissionData The form submission data
     * @param array<object> $formFields The form field objects
     * @return bool
     * @throws ForbiddenException If CAPTCHA validation fails
     */
    public function validateFormSubmission(array $submissionData, array $formFields): bool
    {
        return $this->securityServiceFactory->validateFormSubmission($submissionData, $formFields);
    }

    /**
     * Get security configuration for frontend
     *
     * @param array<object> $formFields Array of form field objects to determine required config
     * @return array<string, mixed>
     */
    public function getFrontendSecurityConfig(array $formFields): array
    {
        $provider = $this->getCaptchaProviderForForm($formFields);

        if ($provider === CaptchaProviderResolver::PROVIDER_NONE) {
            return $this->buildDisabledConfig();
        }

        return $this->buildProviderConfig($provider, $formFields);
    }

    /**
     * Build configuration for disabled CAPTCHA
     *
     * @return array<string, mixed>
     */
    private function buildDisabledConfig(): array
    {
        return [
            'captcha' => [
                'enabled' => false,
                'provider' => CaptchaProviderResolver::PROVIDER_NONE
            ]
        ];
    }

    /**
     * Build provider configuration
     *
     * @param string $provider
     * @param array<object> $formFields
     * @return array<string, mixed>
     */
    private function buildProviderConfig(string $provider, array $formFields): array
    {
        $service = $this->securityServiceFactory->createActiveCaptchaService($formFields);

        $config = ['captcha' => ['enabled' => true, 'provider' => $provider]];

        if ($provider === CaptchaProviderResolver::PROVIDER_RECAPTCHA) {
            $config['captcha']['recaptcha'] = $this->recaptchaConfigProvider->getConfig($service);
        } elseif ($provider === CaptchaProviderResolver::PROVIDER_TURNSTILE) {
            $config['captcha']['turnstile'] = $this->turnstileConfigProvider->getConfig($service);
        } elseif ($provider === CaptchaProviderResolver::PROVIDER_HCAPTCHA) {
            $config['captcha']['hcaptcha'] = $this->hcaptchaConfigProvider->getConfig($service);
        }

        return $config;
    }
}
