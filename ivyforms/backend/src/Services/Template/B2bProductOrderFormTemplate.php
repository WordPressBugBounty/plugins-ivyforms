<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class B2bProductOrderFormTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'b2b_product_order_form',
            'name' => BackendStrings::getTemplateStrings()['b2b_product_order_form'],
            'description' => BackendStrings::getTemplateStrings()['b2b_product_order_form_desc'],
            'category' => 'order-forms',
            'subcategory' => 'product-order-forms',
            'is_pro' => true,
            'required_plan' => 'essentials',
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'b2b-product-order-form.svg',
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
                    'name' => BackendStrings::getTemplateStrings()['b2b_product_order_form'],
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
