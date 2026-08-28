<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class UniversalConstructionChangeFormTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'universal_construction_change_form',
            'name' => BackendStrings::getTemplateStrings()['universal_construction_change_form'],
            'description' => BackendStrings::getTemplateStrings()['universal_construction_change_form_desc'],
            'category' => 'order-forms',
            'subcategory' => 'change-order-forms',
            'is_pro' => true,
            'required_plan' => 'essentials',
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'construction-change-order-form-universal.svg'
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
                'name' => BackendStrings::getTemplateStrings()['universal_construction_change_form'],
                'published' => 1,
                'showTitle' => 1,
                'storeEntries' => 1,
                'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
                'fields' => [],
                'settings' => []
            ]
            ]
        );
    }
}
