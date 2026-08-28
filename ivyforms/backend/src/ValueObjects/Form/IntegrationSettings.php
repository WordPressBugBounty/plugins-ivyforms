<?php

namespace IvyForms\ValueObjects\Form;

use IvyForms\Services\Template\TemplateDefaults;

/**
 * Class IntegrationSettings
 * Encapsulates integration settings for a form.
 *
 * @property array<string, array<string, mixed>> $settings
 */
final class IntegrationSettings
{
    /**
     * @var array<string, array<string, mixed>>
     */
    private array $settings = [];

    /**
     * IntegrationSettings constructor.
     * @param array<string, array<string, mixed>>|null $settings
     */
    public function __construct($settings = null)
    {
        if (!is_array($settings) || empty($settings)) {
            $settings = TemplateDefaults::getDefaultIntegrationSettings();
        }
        $this->settings = $settings;
    }

    /**
     * Get settings for a specific integration.
     * @param string $integration
     * @return array<string, mixed>|null
     */
    public function get(string $integration): ?array
    {
        return $this->settings[$integration] ?? null;
    }

    /**
     * Set settings for a specific integration.
     * @param string $integration
     * @param array<string, mixed> $settings
     */
    public function set(string $integration, array $settings): void
    {
        $this->settings[$integration] = $settings;
    }

    /**
     * Get all integration settings.
     * @return array<string, array<string, mixed>>
     */
    public function getAll(): array
    {
        if (empty($this->settings)) {
            return TemplateDefaults::getDefaultIntegrationSettings();
        }
        return $this->settings;
    }

    /**
     * Get enabled status for any integration.
     * @param string $integration
     * @return bool
     */
    public function isIntegrationEnabled(string $integration): bool
    {
        return !empty($this->settings[$integration]['enabled']);
    }

    /**
     * Set enabled status for any integration.
     * @param string $integration
     * @param bool $enabled
     */
    public function setIntegrationEnabled(string $integration, bool $enabled): void
    {
        if (!isset($this->settings[$integration])) {
            $this->settings[$integration] = [];
        }
        $this->settings[$integration]['enabled'] = $enabled;
    }

    /**
     * Export settings as array.
     * @return array<string, array<string, mixed>>
     */
    public function toArray(): array
    {
        return [
            'integrationSettings' => $this->settings
        ];
    }
}
