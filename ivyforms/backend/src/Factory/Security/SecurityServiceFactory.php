<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

namespace IvyForms\Factory\Security;

use IvyForms\Common\Exceptions\ForbiddenException;
use IvyForms\Services\Security\Common\CaptchaServiceInterface;
use IvyForms\Services\Settings\SettingsService;

/**
 * Main facade for security service management
 *
 * Provides a simplified interface for creating and managing CAPTCHA services
 * while delegating specific responsibilities to specialized classes.
 *
 * @package IvyForms\Factory\Security
 */
class SecurityServiceFactory
{
    private CaptchaServiceFactory $factory;

    private CaptchaProviderRegistry $registry;

    private CaptchaProviderResolver $resolver;

    public function __construct(SettingsService $settingsService)
    {
        $this->factory = new CaptchaServiceFactory($settingsService);
        $this->registry = new CaptchaProviderRegistry();
        $this->resolver = new CaptchaProviderResolver($settingsService);
    }

    /**
     * Get the active CAPTCHA provider from settings
     *
     * Note: This returns the first configured provider, but actual usage
     * depends on which CAPTCHA field is present in the specific form.
     *
     * @return string
     */
    public function getActiveCaptchaProvider(): string
    {
        return $this->resolver->getActiveProvider();
    }

    /**
     * Determine CAPTCHA provider based on form fields and current settings
     *
     * @param array<object> $formFields Array of form field objects
     * @return string The CAPTCHA provider to use for this form
     */
    public function getCaptchaProviderForForm(array $formFields): string
    {
        return $this->resolver->resolveProviderForForm($formFields);
    }

    /**
     * Create the appropriate CAPTCHA service based on active provider
     *
     * @param array<object>|null $formFields Optional form fields to determine provider
     * @return CaptchaServiceInterface|null Returns the service instance or null if no provider is active
     */
    public function createActiveCaptchaService(?array $formFields = null): ?CaptchaServiceInterface
    {
        $provider = ($formFields !== null)
            ? $this->getCaptchaProviderForForm($formFields)
            : $this->getActiveCaptchaProvider();

        if ($provider === CaptchaProviderResolver::PROVIDER_NONE) {
            return null;
        }

        // Use cached instance if available
        if ($this->registry->has($provider)) {
            return $this->registry->get($provider);
        }

        $service = $this->factory->create($provider);
        if ($service !== null) {
            $this->registry->register($provider, $service);
        }

        return $service;
    }

    /**
     * Validate form submission with the appropriate CAPTCHA provider based on form fields
     *
     * @param array<string, mixed> $submissionData The form submission data
     * @param array<object> $formFields The form field objects
     * @return bool
     * @throws ForbiddenException
     */
    public function validateFormSubmission(array $submissionData, array $formFields): bool
    {
        $service = $this->createActiveCaptchaService($formFields);

        if ($service === null) {
            // No CAPTCHA service active for this form, validation passes
            return true;
        }

        return $service->validateFormSubmission($submissionData, $formFields);
    }

    /**
     * Get all supported CAPTCHA providers
     *
     * @return array<string>
     */
    public function getSupportedProviders(): array
    {
        return $this->factory->getSupportedProviders();
    }

    /**
     * Get all configured CAPTCHA providers from database
     *
     * @return array<string>
     */
    public function getConfiguredProviders(): array
    {
        return $this->resolver->getConfiguredProviders();
    }

    /**
     * Get the resolver instance for advanced operations
     *
     * @return CaptchaProviderResolver
     */
    public function getResolver(): CaptchaProviderResolver
    {
        return $this->resolver;
    }

    /**
     * Get the registry instance for cache management
     *
     * @return CaptchaProviderRegistry
     */
    public function getRegistry(): CaptchaProviderRegistry
    {
        return $this->registry;
    }

    /**
     * Get the factory instance for direct service creation
     *
     * @return CaptchaServiceFactory
     */
    public function getFactory(): CaptchaServiceFactory
    {
        return $this->factory;
    }

    /**
     * Clear all cached service instances
     */
    public function clearCache(): void
    {
        $this->registry->clear();
    }
}
