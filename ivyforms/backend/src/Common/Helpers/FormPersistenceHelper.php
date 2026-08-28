<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace IvyForms\Common\Helpers;

use IvyForms\Common\Sanitizer\Sanitizer;
use IvyForms\Services\Translations\BackendStrings;

/**
 * Maps form entity arrays to database row shapes for persistence.
 */
class FormPersistenceHelper
{
    /**
     * Build the JSON settings blob stored in the forms.settings column.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public static function buildSettingsBlob(array $data): array
    {
        $settingsBlob = [
            'showTitle'           => (int) $data['showTitle'],
            'showDescription'     => (int) $data['showDescription'],
            'storeEntries'        => (int) $data['storeEntries'],
            'pages'               => $data['pages'] ?? [],
            'progressIndicator'   => $data['progressIndicator'] ?? [],
            'formActionButtons'   => $data['formActionButtons'] ?? self::defaultFormActionButtons(),
            'paymentSettings'     => Sanitizer::sanitizePaymentSettings($data['paymentSettings'] ?? null),
        ];

        /**
         * Merge form-level extension data into the settings JSON column.
         *
         * @param array<string, mixed> $settingsBlob Core keys written by Lite.
         * @param array<string, mixed> $data           Full form array from entity->toArray().
         */
        return apply_filters('ivyforms/form/persistence/settings_blob', $settingsBlob, $data);
    }

    /**
     * Map form entity data to a database insert row.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public static function buildInsertRow(array $data): array
    {
        return [
            'name'                => $data['name'],
            'author'              => wp_get_current_user()->display_name,
            'starred'             => (int) $data['starred'],
            'published'           => (int) $data['published'],
            'dateCreated'         => current_time('mysql'),
            'dateEdited'          => current_time('mysql'),
            'description'         => $data['description'],
            'formType'            => $data['formType'] ?? 'classic',
            'settings'            => json_encode(self::buildSettingsBlob($data)),
            'integrationSettings' => isset($data['integrationSettings'])
                ? json_encode($data['integrationSettings'])
                : json_encode([]),
            'styleSettings'       => isset($data['styleSettings']) ? json_encode($data['styleSettings']) : null,
        ];
    }

    /**
     * Map form entity data to a database update row.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public static function buildUpdateRow(array $data): array
    {
        return [
            'name'                => $data['name'],
            'starred'             => (int) $data['starred'],
            'published'           => (int) $data['published'],
            'description'         => $data['description'],
            'formType'            => $data['formType'] ?? 'classic',
            'dateEdited'          => current_time('mysql'),
            'settings'            => json_encode(self::buildSettingsBlob($data)),
            'integrationSettings' => isset($data['integrationSettings'])
                ? json_encode($data['integrationSettings'])
                : json_encode([]),
            'styleSettings'       => isset($data['styleSettings']) ? json_encode($data['styleSettings']) : null,
        ];
    }

    /**
     * @return array<string, array<string, string>>
     */
    private static function defaultFormActionButtons(): array
    {
        return [
            'submitButtonSettings' => [
                'label'    => BackendStrings::getCommonStrings()['submit'],
                'position' => 'default',
            ],
        ];
    }
}
