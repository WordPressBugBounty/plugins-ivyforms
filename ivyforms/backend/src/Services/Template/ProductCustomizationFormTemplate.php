<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class ProductCustomizationFormTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'product_customization_form',
            'name' => BackendStrings::getTemplateStrings()['product_customization_form'],
            'description' => BackendStrings::getTemplateStrings()['product_customization_form_desc'],
            'category' => 'order-forms',
            'subcategory' => 'product-order-forms',
            'is_pro' => true,
            'required_plan' => 'essentials',
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'product-customization-order-form.svg',
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
                    'name' => BackendStrings::getTemplateStrings()['product_customization_form'],
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
