<?php

/**
 * Settings hook for activation
 */

namespace IvyForms\Services\InstallActions;

use Exception;
use IvyForms\Services\Settings\SettingsService;

/**
 * Class ActivationSettingsHook
 *
 * @package IvyForms\Services\InstallActions
 */
class ActivationSettingsHook
{
    /**
     * Initialize the plugin
     *
     * @throws Exception
     */
    public static function init(): void
    {
        self::initGeneralSettings();
        self::initIntegrationsSettings();
    }

    /**
     * @param string $category
     * @param mixed[] $settings
     */
    public static function initSettings(string $category, array $settings): void
    {
        $settingsStorage = new SettingsService();

        if (!$settingsStorage->getCategorySettings($category)) {
            $settingsStorage->setCategorySettings(
                $category,
                []
            );
        }

        foreach ($settings as $key => $value) {
            if (null === $settingsStorage->getSetting($category, $key)) {
                $settingsStorage->setSetting(
                    $category,
                    $key,
                    $value
                );
            }
        }
    }

    /**
     * Init General Settings
     */
    private static function initGeneralSettings(): void
    {

        $settings = [
            'version'           => IVYFORMS_VERSION,
            'changelog_lite_version' => '',
            'changelog_pro_version' => '',
            'fullscreen'        => false,
        ];

        self::initSettings('general', $settings);

        // On activation/reactivation, sync stored changelog versions to current versions
        // so changelog is only shown on real version updates.
        $settingsStorage = new SettingsService();
        $settingsStorage->setSetting('general', 'changelog_lite_version', IVYFORMS_VERSION);

        if (defined('IVYFORMS_PRO_VERSION')) {
            $settingsStorage->setSetting('general', 'changelog_pro_version', IVYFORMS_PRO_VERSION);
        }
    }

    /**
     * Init Integrations Settings
     */
    private static function initIntegrationsSettings(): void
    {
        $settings = [
            'wpdatatables' => [
                'enabled' => false,
            ],
            'ameliabooking' => [
                'enabled' => false,
            ],
            'mailchimp' => [
                'enabled' => false,
            ],
            // Google Sheets last — matches integration list/menu ordering
            'google_sheets' => [
                'enabled'        => false,
                'connected'      => false,
                'authType'       => '',
                'accessToken'    => '',
                'refreshToken'   => '',
                'tokenExpiresAt' => 0,
                'accountEmail'   => '',
            ],
        ];

        self::initSettings('integrations', $settings);
    }
}
