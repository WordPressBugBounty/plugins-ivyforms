<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

namespace IvyForms\Factory\Security;

use IvyForms\Services\Security\Common\CaptchaServiceInterface;
use IvyForms\Services\Security\HCaptcha\HCaptchaService;
use IvyForms\Services\Security\Recaptcha\RecaptchaService;
use IvyForms\Services\Security\Turnstile\TurnstileService;
use IvyForms\Services\Settings\SettingsService;

/**
 * Simple factory for creating CAPTCHA service instances
 *
 * Focused on just creating service instances without caching or business logic.
 * Uses composition with Registry and Resolver for advanced functionality.
 *
 * @package IvyForms\Factory\Security
 */
class CaptchaServiceFactory
{
    private SettingsService $settingsService;

    public function __construct(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
    }

    /**
     * Create a CAPTCHA service instance for the given provider
     *
     * @param string $provider The CAPTCHA provider
     * @return CaptchaServiceInterface|null
     */
    public function create(string $provider): ?CaptchaServiceInterface
    {
        switch ($provider) {
            case CaptchaProviderResolver::PROVIDER_RECAPTCHA:
                return new RecaptchaService($this->settingsService);

            case CaptchaProviderResolver::PROVIDER_HCAPTCHA:
                return new HCaptchaService($this->settingsService);

            case CaptchaProviderResolver::PROVIDER_TURNSTILE:
                return new TurnstileService($this->settingsService);

            default:
                return null;
        }
    }

    /**
     * Get all supported CAPTCHA providers
     *
     * @return array<string>
     */
    public function getSupportedProviders(): array
    {
        return [
            CaptchaProviderResolver::PROVIDER_RECAPTCHA,
            CaptchaProviderResolver::PROVIDER_HCAPTCHA,
            CaptchaProviderResolver::PROVIDER_TURNSTILE,
        ];
    }

    /**
     * Check if a provider is supported (has implementation)
     *
     * @param string $provider
     * @return bool
     */
    public function isSupported(string $provider): bool
    {
        return $this->create($provider) !== null;
    }
}
