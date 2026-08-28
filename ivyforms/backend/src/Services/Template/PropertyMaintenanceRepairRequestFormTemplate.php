<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class PropertyMaintenanceRepairRequestFormTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'property_maintenance_repair_request_form',
            'name' => BackendStrings::getTemplateStrings()['property_maintenance_repair_request_form'],
            'description' => BackendStrings::getTemplateStrings()['property_maintenance_repair_request_form_desc'],
            'category' => 'order-forms',
            'subcategory' => 'work-order-forms',
            'is_pro' => true,
            'required_plan' => 'essentials',
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'property-maintenance-repair-request-form.svg',
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
                    'name' => BackendStrings::getTemplateStrings()['property_maintenance_repair_request_form'],
                    'published' => 1,
                    'showTitle' => 1,
                    'storeEntries' => 1,
                    'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
                    'fields' => [],
                    'settings' => [],
                ],
            ]
        );
    }
}
