<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class BlankFormTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'blank_form',
            'name' => BackendStrings::getSettingsFormBuilderStrings()['blank_form'],
            'description' => '',
            'category' => 'basic',
            'is_pro' => false,
            'screenshot' => ''
        ];
    }

    /**
     * Get the full template including form data
     *
     * @return array<string, mixed>
     */
    public static function getTemplate(): array
    {
        return array_merge(
            self::getTemplateMeta(),
            [
            'form_data' => [
                'name' => BackendStrings::getSettingsFormBuilderStrings()['blank_form'],
                'description' => '',
                'published'     => 1,
                'showTitle'     => 1,
                'storeEntries'  => 1,
                'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
                'fields' => [],
                'settings' => []
            ]
            ]
        );
    }
}
