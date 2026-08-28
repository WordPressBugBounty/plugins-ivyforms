<?php

namespace IvyForms\Services\GoogleSheets\Hooks;

use IvyForms\Routes\GoogleSheets\GoogleSheets;
use IvyForms\Services\Settings\SettingsService;
use IvyForms\Vendor\DI\Container;

/**
 * Initializes Google Sheets integration settings, hooks, and routes.
 */
class InitializationHook
{
    private SettingsService $settingsService;

    public function __construct(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
    }

    public function initialize(): void
    {
        $this->initializeSettings();
        $this->registerSettingsFilter();
        $this->registerHooks();
        $this->registerRoutes();
    }

    private function registerSettingsFilter(): void
    {
        $settingsHook = new SettingsHook();

        add_filter(
            'ivyforms/global/settings/get_all',
            [$settingsHook, 'addIntegrationData'],
            10,
            2
        );
    }

    private function initializeSettings(): void
    {
        $settings = $this->settingsService->getSetting('integrations', 'google_sheets');

        if ($settings === null) {
            $this->settingsService->setSetting('integrations', 'google_sheets', [
                'enabled'        => false,
                'connected'      => false,
                'authType'       => '',
                'accessToken'    => '',
                'refreshToken'   => '',
                'tokenExpiresAt' => 0,
                'accountEmail'   => '',
            ]);
        }
    }

    private function registerHooks(): void
    {
        try {
            $basePlugin = \IvyForms\Plugin\Plugin::getInstance();
            if (!$basePlugin || !$basePlugin->container) {
                return;
            }

            $entryHooks = $basePlugin->container->get(GoogleSheetsEntryHooks::class);
            $entryHooks->register();

            $formHooks = $basePlugin->container->get(GoogleSheetsFormHooks::class);
            $formHooks->register();
        } catch (\Exception $e) {
            error_log('IvyForms: Failed to register Google Sheets hooks: ' . $e->getMessage());
        }
    }

    private function registerRoutes(): void
    {
        add_action(
            'ivyforms/rest/register_additional_routes',
            static function (Container $container, string $namespace): void {
                GoogleSheets::registerRoutes($container, $namespace);
            },
            10,
            2
        );
    }
}
