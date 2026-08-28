<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class CustomEventTShirtBulkOrderFormTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'custom_event_tshirt_bulk_order_form',
            'name' => BackendStrings::getTemplateStrings()['custom_event_tshirt_bulk_order_form'],
            'description' => BackendStrings::getTemplateStrings()['custom_event_tshirt_bulk_order_form_desc'],
            'category' => 'order-forms',
            'subcategory' => 'tshirt-order-forms',
            'is_pro' => true,
            'required_plan' => 'essentials',
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'custom-event-tshirt-bulk-order-form.svg',
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
                    'name' => BackendStrings::getTemplateStrings()['custom_event_tshirt_bulk_order_form'],
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
