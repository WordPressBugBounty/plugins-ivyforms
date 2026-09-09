<?php

namespace IvyForms\Services\Integrations;

use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Services\GoogleSheets\Hooks\InitializationHook as GoogleSheetsInitializationHook;

/**
 * Integration Service
 *
 * Initializes the integration registry and registers default Lite integrations
 *
 * @since 1.0.0
 */
class IntegrationService
{
    private IntegrationRegistry $registry;
    private GoogleSheetsInitializationHook $googleSheetsInitializationHook;

    public function __construct(
        IntegrationRegistry $registry,
        GoogleSheetsInitializationHook $googleSheetsInitializationHook
    ) {
        $this->registry = $registry;
        $this->googleSheetsInitializationHook = $googleSheetsInitializationHook;
    }

    /**
     * Initialize integrations
     *
     * @return void
     * @throws InvalidArgumentException
     */
    public function init(): void
    {
        // Register Lite integrations
        $this->registerLiteIntegrations();

        /**
         * Allow Pro/addons to register their integrations
         *
         * @since 1.0.0
         *
         * @param IntegrationRegistry $registry The integration registry instance
         */
        do_action('ivyforms/integrations/register', $this->registry);

        $this->initializeGoogleSheets();
    }

    private function initializeGoogleSheets(): void
    {
        $this->googleSheetsInitializationHook->initialize();
    }

    /**
     * Register default Lite integrations
     *
     * @return void
     * @throws InvalidArgumentException
     */
    private function registerLiteIntegrations(): void
    {
        // wpDataTables Integration (Lite)
        $this->registry->register('wpdatatables', [
            'label' => 'wpdatatables_label',
            'component' => 'WpDataTablesIntegrationSettings',
            'icon' => 'wpdatatables',
            'description' => 'wpdatatables_description',
            'category' => 'Data Management',
            'requiresAuth' => false,
            'plan' => 'lite',
            'settingsSchema' => [
                'enabled' => [
                    'type' => 'boolean',
                    'default' => false,
                ],
            ],
        ]);

        // Amelia Booking Integration (Lite)
        $this->registry->register('ameliabooking', [
            'label' => 'ameliabooking_label',
            'component' => 'AmeliaBookingIntegrationSettings',
            'icon' => 'ameliabooking',
            'description' => 'ameliabooking_description',
            'category' => 'Booking',
            'requiresAuth' => false,
            'hasFormSettings' => false,
            'plan' => 'lite',
            'settingsSchema' => [
                'enabled' => [
                    'type' => 'boolean',
                    'default' => false,
                ],
            ],
        ]);

        $this->registerPageBuilderIntegrations();

        // Register Pro integrations as placeholders
        // These will be shown with "Upgrade" button when Pro is not installed
        // When Pro is installed, these will be overwritten by Pro's registrar

        // MailChimp Integration (Pro - Essentials)
        $this->registry->register('mailchimp', [
            'label' => 'mailchimp_label',
            'component' => 'MailChimpIntegrationSettings',
            'icon' => 'mailchimp',
            'description' => 'mailchimp_description',
            'category' => 'Email Marketing',
            'requiresAuth' => true,
            'hasGlobalSettings' => true,
            'plan' => 'essentials',
        ]);

        // Webhooks Integration (Pro - Growth)
        $this->registry->register('webhooks', [
            'label' => 'webhooks_label',
            'component' => 'WebhooksIntegrationSettings',
            'icon' => 'webhooks',
            'description' => 'webhooks_description',
            'category' => 'Developer Tools',
            'requiresAuth' => false,
            'hasGlobalSettings' => false,
            'plan' => 'growth',
            'learnMoreUrl' => 'https://ivyforms.com/documentation/webhooks-integration/',
            'settingsSchema' => [
                'enabled' => [
                    'type' => 'boolean',
                    'default' => false,
                ],
            ],
        ]);

        // Zapier Integration (Pro - Growth)
        $this->registry->register('zapier', [
            'label' => 'zapier_label',
            'component' => 'ZapierIntegrationSettings',
            'icon' => 'zapier',
            'description' => 'zapier_description',
            'category' => 'Automation',
            'requiresAuth' => false,
            'hasGlobalSettings' => true,
            'plan' => 'growth',
        ]);

        // Public API Integration (Pro - Agency)
        $this->registry->register('public-api', [
            'label' => 'public_api_integration_label',
            'component' => 'PublicApiSettings',
            'icon' => 'public-api',
            'description' => 'public_api_integration_description',
            'category' => 'Developer Tools',
            'requiresAuth' => false,
            'hasGlobalSettings' => true,
            'hasFormSettings' => false,
            'plan' => 'agency',
            'learnMoreUrl' => 'https://ivyforms.com/documentation/public-rest-api/',
            'settingsSchema' => [
                'enabled' => [
                    'type' => 'boolean',
                    'default' => false,
                ],
            ],
        ]);

        // Salesforce Integration (Pro - Agency)
//        $this->registry->register('salesforce', [
//            'label' => 'salesforce_label',
//            'component' => 'SalesforceIntegrationSettings',
//            'icon' => 'salesforce',
//            'description' => 'salesforce_description',
//            'category' => 'CRM',
//            'requiresAuth' => true,
//            'plan' => 'agency',
//        ]);

        // Google Sheets Integration (Lite) — registered last so it appears last in UI lists
        $this->registry->register('google_sheets', [
            'label' => 'google_sheets_label',
            'component' => 'GoogleSheetsIntegrationSettings',
            'icon' => 'google_sheets',
            'description' => 'google_sheets_description',
            'category' => 'Data Management',
            'requiresAuth' => true,
            'hasGlobalSettings' => true,
            'plan' => 'lite',
            'learnMoreUrl' => 'https://ivyforms.com/documentation/integrations/google-sheets/',
            'settingsSchema' => [
                'enabled' => [
                    'type' => 'boolean',
                    'default' => false,
                ],
            ],
        ]);
    }

    /**
     * Register page builder integrations (Lite)
     *
     * These are informational cards only: forms are embedded through the builder itself,
     * so there is nothing to enable or configure — the card just links to the documentation.
     *
     * @return void
     * @throws InvalidArgumentException
     */
    private function registerPageBuilderIntegrations(): void
    {
        $pageBuilderSlugs = ['elementor', 'divi', 'gutenberg'];

        foreach ($pageBuilderSlugs as $slug) {
            $this->registry->register($slug, [
                'label' => $slug . '_label',
                'icon' => $slug,
                'description' => $slug . '_description',
                'category' => 'Page Builders',
                'plan' => 'lite',
                'docsOnly' => true,
                'hasGlobalSettings' => false,
                'hasFormSettings' => false,
                'learnMoreUrl' => 'https://ivyforms.com/documentation/',
            ]);
        }
    }
}
