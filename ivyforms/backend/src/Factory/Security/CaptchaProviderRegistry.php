<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

namespace IvyForms\Factory\Security;

use IvyForms\Services\Security\Common\CaptchaServiceInterface;

/**
 * Registry for managing CAPTCHA service instances
 *
 * Handles caching and lifecycle management of CAPTCHA service instances
 * to avoid unnecessary object creation and improve performance.
 *
 * @package IvyForms\Factory\Security
 */
class CaptchaProviderRegistry
{
    /**
     * @var array<string, CaptchaServiceInterface>
     */
    private array $services = [];

    /**
     * Register a service instance for a provider
     *
     * @param string $provider The provider identifier
     * @param CaptchaServiceInterface $service The service instance
     */
    public function register(string $provider, CaptchaServiceInterface $service): void
    {
        $this->services[$provider] = $service;
    }

    /**
     * Get a registered service instance
     *
     * @param string $provider The provider identifier
     * @return CaptchaServiceInterface|null
     */
    public function get(string $provider): ?CaptchaServiceInterface
    {
        return $this->services[$provider] ?? null;
    }

    /**
     * Check if a provider is registered
     *
     * @param string $provider The provider identifier
     * @return bool
     */
    public function has(string $provider): bool
    {
        return isset($this->services[$provider]);
    }

    /**
     * Remove a provider from the registry
     *
     * @param string $provider The provider identifier
     */
    public function remove(string $provider): void
    {
        unset($this->services[$provider]);
    }

    /**
     * Clear all registered services
     */
    public function clear(): void
    {
        $this->services = [];
    }

    /**
     * Get all registered provider names
     *
     * @return array<string>
     */
    public function getRegisteredProviders(): array
    {
        return array_keys($this->services);
    }

    /**
     * Get count of registered services
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->services);
    }
}
