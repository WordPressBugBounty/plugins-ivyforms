<?php

namespace IvyForms\Services\Shortcode\Helpers;

use IvyForms\Plugin\Plugin;
use IvyForms\Services\Payment\SquareEnvironment;
use IvyForms\Services\Payment\SquareOAuthService;
use IvyForms\Services\Payment\SquarePaymentService;
use IvyForms\Services\Settings\SettingsService;

class SquareFrontendConfigHelper
{
    /**
     * @param array<string, array<string, mixed>> $formList
     */
    public static function localizeConfigIfNeeded(string $scriptId, array $formList): void
    {
        if (!self::formHasPaymentField($formList)) {
            return;
        }

        $settingsService = new SettingsService();
        $squareSettings = self::readSquareSettings($settingsService);
        [$locationId, $squareSettings] = self::resolveSquareLocationId($settingsService, $squareSettings);
        $applicationId = self::resolveSquareApplicationId();

        wp_localize_script(
            $scriptId,
            'wpIvySquareConfig',
            [
                'applicationId' => $applicationId,
                'locationId' => $locationId !== '' ? $locationId : ($squareSettings['locationId'] ?? ''),
                'currency' => strtoupper((string) ($squareSettings['currency'] ?? '')),
                'connected' => !empty($squareSettings['connected']),
                'sdkUrl' => SquareEnvironment::getWebPaymentsSdkUrl(),
                'sandbox' => SquareEnvironment::isSandbox(),
            ]
        );
    }

    /**
     * @param array<string, array<string, mixed>> $formList
     */
    private static function formHasPaymentField(array $formList): bool
    {
        foreach ($formList as $formData) {
            foreach ($formData['fields'] ?? [] as $field) {
                if (is_array($field) && ($field['type'] ?? '') === 'payment-method') {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @return array<string, mixed>
     */
    private static function readSquareSettings(SettingsService $settingsService): array
    {
        $settings = $settingsService->getSetting('payment', 'square');

        return is_array($settings) ? $settings : [];
    }

    /**
     * @param array<string, mixed> $squareSettings
     * @return array{0:string,1:array<string,mixed>}
     */
    private static function resolveSquareLocationId(SettingsService $settingsService, array $squareSettings): array
    {
        $locationId = (string) ($squareSettings['locationId'] ?? '');
        if (!self::shouldFetchSquareLocationId($locationId, $squareSettings)) {
            return [$locationId, $squareSettings];
        }

        $location = self::fetchPrimarySquareLocation();
        $locationId = (string) ($location['id'] ?? '');
        if ($locationId === '') {
            return ['', $squareSettings];
        }

        $squareSettings['locationId'] = $locationId;
        $squareSettings['locationName'] = (string) ($location['name'] ?? ($squareSettings['locationName'] ?? ''));
        $squareSettings['currency'] = (string) ($location['currency'] ?? ($squareSettings['currency'] ?? 'USD'));
        $settingsService->setSetting('payment', 'square', $squareSettings);

        return [$locationId, $squareSettings];
    }

    /**
     * @param array<string, mixed> $squareSettings
     */
    private static function shouldFetchSquareLocationId(string $locationId, array $squareSettings): bool
    {
        return $locationId === '' && !empty($squareSettings['connected']);
    }

    /**
     * @return array<string, mixed>
     */
    private static function fetchPrimarySquareLocation(): array
    {
        $plugin = Plugin::getInstance();
        if ($plugin->container === null) {
            return [];
        }

        try {
            $squarePaymentService = $plugin->container->get(SquarePaymentService::class);
            $location = $squarePaymentService->getPrimaryLocationFromApi();

            return is_array($location) ? $location : [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    private static function resolveSquareApplicationId(): string
    {
        $plugin = Plugin::getInstance();
        if ($plugin->container !== null) {
            try {
                $squareOAuthService = $plugin->container->get(SquareOAuthService::class);
                return $squareOAuthService->getPublicApplicationId();
            } catch (\Throwable $e) {
                // Fall through to constant fallback.
            }
        }

        return defined('IVYFORMS_SQUARE_APP_ID') ? (string) IVYFORMS_SQUARE_APP_ID : '';
    }
}
