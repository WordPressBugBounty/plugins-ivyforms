<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class MaterialPurchaseOrderFormTemplate
{
/**
 * Material Purchase Order Form Template
 */
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'material_purchase_order_form',
            'name' => BackendStrings::getTemplateStrings()['material_purchase_order_form'],
            'description' => BackendStrings::getTemplateStrings()[
                'material_purchase_order_form_description'
            ],
            'category' => 'order-forms',
            'subcategory' => 'product-order-forms',
            'is_pro' => false,
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'material-purchase-order-form.svg'
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
                'name' => BackendStrings::getTemplateStrings()['material_purchase_order_form'],
                'published' => 1,
                'showTitle' => 1,
                'storeEntries' => 1,
                'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
                'fields' => array_merge(
                    self::getRequestorInformationFields(),
                    self::getMaterialDetailsFields(),
                    self::getAdditionalInformationFields()
                ),
                'settings' => []
            ]
            ]
        );
    }

    /**
     * Requestor information fields (name, email, phone, department)
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getRequestorInformationFields(): array
    {
        return [
            // Requestor Name
            [
                'id' => 1,
                'fieldIndex' => 1,
                'type' => 'name',
                'label' => BackendStrings::getTemplateStrings()['requestor_name'],
                'parentId' => null,
                'defaultValue' => '',
                'placeholder' => '',
                'required' => true,
                'readonly' => false,
                'position' => 1,
            ],
            // First Name - Child of Name Field
            [
                'id' => 2,
                'type' => 'text',
                'label' => BackendStrings::getTemplateStrings()['first_name'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_your_first_name'],
                'required' => true,
                'parentId' => 1,
                'fieldIndex' => 1,
                'defaultValue' => '',
                'position' => 1,
                'optionHide' => false,
                'description' => '',
                'settings' => json_encode(['nameFieldType' => 'nameField1']),
            ],
            // Last Name - Child of Name Field
            [
                'id' => 3,
                'type' => 'text',
                'label' => BackendStrings::getTemplateStrings()['last_name'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_your_last_name'],
                'required' => true,
                'parentId' => 1,
                'fieldIndex' => 1,
                'defaultValue' => '',
                'position' => 2,
                'optionHide' => false,
                'description' => '',
                'settings' => json_encode(['nameFieldType' => 'nameField2']),
            ],
            // Email Address
            [
                'id' => 4,
                'fieldIndex' => 2,
                'type' => 'email',
                'label' => BackendStrings::getTemplateStrings()['email_address'],
                'placeholder' => BackendStrings::getTemplateStrings()['email_placeholder'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 3,
            ],
            // Phone Number
            [
                'id' => 5,
                'fieldIndex' => 3,
                'type' => 'phone',
                'label' => BackendStrings::getTemplateStrings()['phone_number'],
                'placeholder' => BackendStrings::getTemplateStrings()['phone_placeholder'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 4,
            ],
            // Department
            [
                'id' => 6,
                'fieldIndex' => 4,
                'type' => 'text',
                'label' => BackendStrings::getTemplateStrings()['department'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_department'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 5,
            ],
        ];
    }

    /**
     * Material details fields (material name, quantity, material type)
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getMaterialDetailsFields(): array
    {
        return [
            // Material Name
            [
                'id' => 7,
                'fieldIndex' => 5,
                'type' => 'text',
                'label' => BackendStrings::getTemplateStrings()['material_name'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_material_name'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 6,
            ],
            // Quantity
            [
                'id' => 8,
                'fieldIndex' => 6,
                'type' => 'number',
                'label' => BackendStrings::getTemplateStrings()['quantity'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_quantity'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 7,
                'minValue' => 1,
            ],
            // Material Type
            [
                'id' => 13,
                'fieldIndex' => 7,
                'type' => 'select',
                'label' => BackendStrings::getTemplateStrings()['material_type'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_material_type'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 8,
                'fieldOptions' => [
                    [
                        'id' => 9,
                        'label' => BackendStrings::getTemplateStrings()['raw_materials'],
                        'value' => 'raw_materials',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 10,
                        'label' => BackendStrings::getTemplateStrings()['construction'],
                        'value' => 'construction',
                        'isDefault' => false,
                        'position' => 2
                    ],
                    [
                        'id' => 11,
                        'label' => BackendStrings::getTemplateStrings()['packaging'],
                        'value' => 'packaging',
                        'isDefault' => false,
                        'position' => 3
                    ],
                    [
                        'id' => 12,
                        'label' => BackendStrings::getTemplateStrings()['other'],
                        'value' => 'other',
                        'isDefault' => false,
                        'position' => 4
                    ],
                ]
            ],
        ];
    }

    /**
     * Additional information fields (notes, delivery date)
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getAdditionalInformationFields(): array
    {
        return [
            // Additional Notes
            [
                'id' => 14,
                'fieldIndex' => 8,
                'type' => 'textarea',
                'label' => BackendStrings::getTemplateStrings()['additional_notes'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_additional_notes'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 9,
            ],
            // Required Delivery Date
            [
                'id' => 15,
                'fieldIndex' => 9,
                'type' => 'date',
                'label' => BackendStrings::getTemplateStrings()['required_delivery_date'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_required_delivery_date'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 10,
                'dateFieldType' => 'picker',
                'dateFormat' => 'MM/DD/YYYY',
            ],
        ];
    }
}
