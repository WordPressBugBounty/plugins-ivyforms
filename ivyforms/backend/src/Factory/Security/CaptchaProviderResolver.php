<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

namespace IvyForms\Factory\Security;

use IvyForms\Services\Settings\SettingsService;

/**
 * Resolver for determining which CAPTCHA provider to use
 *
 * Handles the business logic of provider selection based on form fields,
 * configuration validation, and conflict detection.
 *
 * @package IvyForms\Factory\Security
 */
class CaptchaProviderResolver
{
    // Provider constants
    public const PROVIDER_RECAPTCHA = 'recaptcha';
    public const PROVIDER_HCAPTCHA  = 'hcaptcha';
    public const PROVIDER_TURNSTILE = 'turnstile';
    public const PROVIDER_NONE      = 'none';
    private SettingsService $settingsService;

    /** @var array<string, array<string>> */
    private array $requiredKeys;

    /** @var array<string, string> */
    private array $fieldTypeMapping;

    /**
     * @param SettingsService $settingsService
     * @param array<string, array<string>> $requiredKeys Provider required keys mapping
     * @param array<string, string> $fieldTypeMapping Field type to provider mapping
     */
    public function __construct(
        SettingsService $settingsService,
        array $requiredKeys = [],
        array $fieldTypeMapping = []
    ) {
        $this->settingsService = $settingsService;
        $this->requiredKeys = $requiredKeys ?: $this->getDefaultRequiredKeys();
        $this->fieldTypeMapping = $fieldTypeMapping ?: $this->getDefaultFieldTypeMapping();
    }

    /**
     * Get the first configured provider
     *
     * @return string
     */
    public function getActiveProvider(): string
    {
        $providers = [
            self::PROVIDER_RECAPTCHA,
            self::PROVIDER_HCAPTCHA,
            self::PROVIDER_TURNSTILE,
        ];

        foreach ($providers as $provider) {
            if ($this->isConfigured($provider)) {
                return $provider;
            }
        }

        return self::PROVIDER_NONE;
    }

    /**
     * Determine CAPTCHA provider based on form fields
     *
     * @param array<object> $formFields Array of form field objects
     * @return string
     */
    public function resolveProviderForForm(array $formFields): string
    {
        foreach ($formFields as $field) {
            $fieldType = $field->getType();
            if (isset($this->fieldTypeMapping[$fieldType])) {
                $provider = $this->fieldTypeMapping[$fieldType];
                if ($this->isConfigured($provider)) {
                    return $provider;
                }
            }
        }

        return self::PROVIDER_NONE;
    }

    /**
     * Check if a provider is properly configured
     *
     * @param string $provider
     * @return bool
     */
    public function isConfigured(string $provider): bool
    {
        if (!isset($this->requiredKeys[$provider])) {
            return false;
        }

        $settings = $this->settingsService->getSetting('security', $provider);
        if (!is_array($settings)) {
            return false;
        }

        $requiredKeys = $this->requiredKeys[$provider];
        foreach ($requiredKeys as $key) {
            if (empty($settings[$key])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get all configured providers
     *
     * @return array<string>
     */
    public function getConfiguredProviders(): array
    {
        $configured = [];
        $allProviders = [
            self::PROVIDER_RECAPTCHA,
            self::PROVIDER_HCAPTCHA,
            self::PROVIDER_TURNSTILE,
        ];

        foreach ($allProviders as $provider) {
            if ($this->isConfigured($provider)) {
                $configured[] = $provider;
            }
        }

        return $configured;
    }

    /**
     * Check if form has multiple CAPTCHA field types (conflict detection)
     *
     * @param array<object> $formFields Array of form field objects
     * @return array{hasConflict: bool, foundTypes: array<string>}
     */
    public function checkFormCaptchaConflicts(array $formFields): array
    {
        $foundTypes = [];

        foreach ($formFields as $field) {
            $fieldType = $field->getType();
            if (isset($this->fieldTypeMapping[$fieldType])) {
                if (!in_array($fieldType, $foundTypes, true)) {
                    $foundTypes[] = $fieldType;
                }
            }
        }

        return [
            'hasConflict' => count($foundTypes) > 1,
            'foundTypes'  => $foundTypes,
        ];
    }

    /**
     * Get provider status summary
     *
     * @param array<string> $supportedProviders List of supported providers from factory
     * @return array<string, array{supported: bool, configured: bool}>
     */
    public function getProviderStatus(array $supportedProviders = []): array
    {
        $status = [];
        $allProviders = [
            self::PROVIDER_RECAPTCHA,
            self::PROVIDER_HCAPTCHA,
            self::PROVIDER_TURNSTILE,
        ];

        foreach ($allProviders as $provider) {
            $status[$provider] = [
                'supported'  => in_array($provider, $supportedProviders, true),
                'configured' => $this->isConfigured($provider),
            ];
        }

        return $status;
    }

    /**
     * Get configuration requirements for a provider
     *
     * @param string $provider
     * @return array<string>
     */
    public function getProviderRequiredKeys(string $provider): array
    {
        return $this->requiredKeys[$provider] ?? [];
    }

    /**
     * Get field type mapping
     *
     * @return array<string, string>
     */
    public function getFieldTypeMapping(): array
    {
        return $this->fieldTypeMapping;
    }

    /**
     * Get default required keys for providers
     *
     * @return array<string, array<string>>
     */
    private function getDefaultRequiredKeys(): array
    {
        return [
            self::PROVIDER_RECAPTCHA => ['siteKey', 'secretKey'],
            self::PROVIDER_HCAPTCHA  => ['siteKey', 'secretKey'],
            self::PROVIDER_TURNSTILE => ['siteKey', 'secretKey'],
        ];
    }

    /**
     * Get default field type to provider mapping
     *
     * @return array<string, string>
     */
    private function getDefaultFieldTypeMapping(): array
    {
        return [
            'recaptcha' => self::PROVIDER_RECAPTCHA,
            'hcaptcha'  => self::PROVIDER_HCAPTCHA,
            'turnstile' => self::PROVIDER_TURNSTILE,
        ];
    }
}
