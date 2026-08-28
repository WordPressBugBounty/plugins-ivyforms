<?php

namespace IvyForms\Services\Integrations;

use IvyForms\Common\Exceptions\InvalidArgumentException;

/**
 * Integration Registry Service
 *
 * Central registry for all integrations (both Lite and Pro).
 * Allows dynamic registration of integrations without hardcoding.
 *
 * @since 1.0.0
 */
class IntegrationRegistry
{
    /**
     * Registered integrations
     *
     * @var mixed[]
     */
    private array $integrations = [];

    /**
     * Register an integration
     *
     * @param string $slug Integration slug (e.g., 'mailchimp', 'convertkit')
     * @param mixed[] $config Integration configuration
     * @return void
     * @throws InvalidArgumentException
     */
    public function register(string $slug, array $config): void
    {
        // Validate required fields
        if (empty($config['label'])) {
            throw new InvalidArgumentException("Integration '$slug' must have a label");
        }

        if (empty($config['component'])) {
            throw new InvalidArgumentException("Integration '$slug' must have a component name");
        }

        // Merge with defaults
        $this->integrations[$slug] = array_merge([
            'slug' => $slug,
            'icon' => 'integration',
            'description' => '',
            'requiresAuth' => false,
            'hasFormSettings' => true,
            'settingsSchema' => [],
            'plan' => 'lite', // 'lite', 'essentials', 'growth', 'agency'
            'learnMoreUrl' => '', // Optional documentation/learn more URL
        ], $config);
    }

    /**
     * Get all registered integrations
     *
     * @return mixed[]
     */
    public function getAll(): array
    {
        return $this->integrations;
    }

    /**
     * Get a specific integration by slug
     *
     * @param string $slug
     * @return mixed[]|null
     */
    public function get(string $slug): ?array
    {
        return $this->integrations[$slug] ?? null;
    }

    /**
     * Check if an integration is registered
     *
     * @param string $slug
     * @return bool
     */
    public function has(string $slug): bool
    {
        return isset($this->integrations[$slug]);
    }

    /**
     * Get integrations by plan
     *
     * @param string|mixed[] $plans Plan name(s) to filter by
     * @return mixed[]
     */
    public function getByPlan($plans): array
    {
        $plans = (array) $plans;

        return array_filter($this->integrations, function ($integration) use ($plans) {
            return in_array($integration['plan'], $plans);
        });
    }

    /**
     * Unregister an integration
     *
     * @param string $slug
     * @return void
     */
    public function unregister(string $slug): void
    {
        unset($this->integrations[$slug]);
    }
}
