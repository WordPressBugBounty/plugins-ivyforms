<?php

namespace IvyForms\Services\GoogleSheets\Hooks;

/**
 * Injects Google Sheets integration data into the global settings API response.
 */
class SettingsHook
{
    /**
     * @param array<string, mixed> $organizedSettings
     * @param array<string, mixed> $allSettings
     * @return array<string, mixed>
     */
    public function addIntegrationData(array $organizedSettings, array $allSettings): array
    {
        $googleSheetsSettings = $allSettings['integrations']['google_sheets'] ?? null;

        if (!is_array($googleSheetsSettings)) {
            $organizedSettings['integrations']['google_sheets'] = [
                'enabled'   => false,
                'connected' => false,
            ];

            return $organizedSettings;
        }

        $organizedSettings['integrations']['google_sheets'] = [
            'enabled'   => !empty($googleSheetsSettings['enabled']),
            'connected' => !empty($googleSheetsSettings['refreshToken']),
        ];

        return $organizedSettings;
    }
}
