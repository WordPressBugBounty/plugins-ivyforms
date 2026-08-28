<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class ProductOrderFormTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'product_order_form',
            'name' => BackendStrings::getTemplateStrings()['product_order_form'],
            'description' => BackendStrings::getTemplateStrings()['product_order_form_desc'],
            'category' => 'order-forms',
            'subcategory' => 'product-order-forms',
            'is_pro' => false,
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'product-order-form.svg'
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
                'name' => BackendStrings::getTemplateStrings()['product_order_form'],
                'published' => 1,
                'showTitle' => 1,
                'storeEntries' => 1,
                'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
                'fields' => array_merge(
                    self::getProductSelectionFields(),
                    self::getCustomerContactFields(),
                    self::getShippingAddressFields(),
                    self::getShippingMethodFields(),
                    self::getReviewParagraphField()
                ),
                'settings' => []
            ]
            ]
        );
    }

    /**
     * Get product selection fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getProductSelectionFields(): array
    {
        return [
            // Product Selection
            [
                'id' => 6,
                'fieldIndex' => 1,
                'type' => 'select',
                'label' => BackendStrings::getTemplateStrings()['product'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_product'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 1,
                'fieldOptions' => [
                    [
                        'id' => 1,
                        'label' => BackendStrings::getTemplateStrings()['product_a'],
                        'value' => 'product_a',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 2,
                        'label' => BackendStrings::getTemplateStrings()['product_b'],
                        'value' => 'product_b',
                        'isDefault' => false,
                        'position' => 2
                    ],
                    [
                        'id' => 3,
                        'label' => BackendStrings::getTemplateStrings()['product_c'],
                        'value' => 'product_c',
                        'isDefault' => false,
                        'position' => 3
                    ],
                    [
                        'id' => 4,
                        'label' => BackendStrings::getTemplateStrings()['product_d'],
                        'value' => 'product_d',
                        'isDefault' => false,
                        'position' => 4
                    ],
                    [
                        'id' => 5,
                        'label' => BackendStrings::getTemplateStrings()['product_e'],
                        'value' => 'product_e',
                        'isDefault' => false,
                        'position' => 5
                    ],
                ]
            ],
            // Quantity
            [
                'id' => 7,
                'fieldIndex' => 2,
                'type' => 'number',
                'label' => BackendStrings::getTemplateStrings()['quantity'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_quantity'],
                'required' => true,
                'parentId' => null,
                'position' => 2,
                'minValue' => 1,
                'maxValue' => 100,
            ],
        ];
    }

    /**
     * Get customer contact fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getCustomerContactFields(): array
    {
        return [
            // Name Field - Parent
            [
                'id' => 8,
                'fieldIndex' => 3,
                'type' => 'name',
                'label' => BackendStrings::getTemplateStrings()['customer_name'],
                'parentId' => null,
                'defaultValue' => '',
                'placeholder' => '',
                'readonly' => false,
                'position' => 3,
                'required' => true,
            ],
            // First Name - Child of Name Field
            [
                'id' => 9,
                'type' => 'text',
                'label' => BackendStrings::getNewFormStrings()['first_name'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_your_first_name'],
                'required' => true,
                'parentId' => 3,
                'defaultValue' => '',
                'position' => 1,
                'optionHide' => false,
                'description' => '',
                'settings' => json_encode(['nameFieldType' => 'nameField1']),
            ],
            // Last Name - Child of Name Field
            [
                'id' => 10,
                'type' => 'text',
                'label' => BackendStrings::getNewFormStrings()['last_name'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_your_last_name'],
                'required' => true,
                'parentId' => 3,
                'defaultValue' => '',
                'position' => 2,
                'optionHide' => false,
                'description' => '',
                'settings' => json_encode(['nameFieldType' => 'nameField2']),
            ],
            // Email
            [
                'id' => 11,
                'fieldIndex' => 4,
                'type' => 'email',
                'label' => BackendStrings::getNewFormStrings()['email'],
                'placeholder' => BackendStrings::getTemplateStrings()['email_address'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 4,
            ],
            // Phone
            [
                'id' => 12,
                'fieldIndex' => 5,
                'type' => 'phone',
                'label' => BackendStrings::getTemplateStrings()['phone_mobile'],
                'placeholder' => '+1 (_) _-_',
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 5,
                'phoneAutoDetect' => true,
            ],
        ];
    }

    /**
     * Get shipping address fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getShippingAddressFields(): array
    {
        return [
            // Address Field - Parent
            [
                'id' => 13,
                'fieldIndex' => 6,
                'type' => 'address',
                'label' => BackendStrings::getTemplateStrings()['shipping_address'],
                'parentId' => null,
                'defaultValue' => '',
                'placeholder' => '',
                'readonly' => false,
                'position' => 6,
                'required' => true,
            ],
            // Street Address - Child of Address Field
            [
                'id' => 15,
                'type' => 'text',
                'label' => BackendStrings::getTemplateStrings()['address_line_1'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_address_line_1'],
                'required' => true,
                'parentId' => 6,
                'fieldIndex' => 6,
                'defaultValue' => '',
                'position' => 6,
                'hideLabel' => false,
                'description' => '',
                'addressType' => 'streetAddress',
                'settings' => json_encode(
                    [
                    'type' => 'streetAddress',
                    'hideLabel' => false,
                    'description' => '',
                    'placeholder' => BackendStrings::getTemplateStrings()['address_line_1'],
                    'requiredMessage' => '',
                    'visible' => true
                    ]
                ),
            ],
            // Address Line 2 - Child of Address Field
            [
                'id' => 17,
                'type' => 'text',
                'label' => BackendStrings::getTemplateStrings()['address_line_2'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_address_line_2'],
                'required' => false,
                'parentId' => 6,
                'fieldIndex' => 6,
                'defaultValue' => '',
                'position' => 6,
                'hideLabel' => false,
                'description' => '',
                'addressType' => 'addressLine2',
                'settings' => json_encode(
                    [
                    'type' => 'addressLine2',
                    'hideLabel' => false,
                    'description' => '',
                    'placeholder' => BackendStrings::getTemplateStrings()['address_line_2'],
                    'requiredMessage' => '',
                    'visible' => true
                    ]
                ),
            ],
            // City - Child of Address Field
            [
                'id' => 19,
                'type' => 'text',
                'label' => BackendStrings::getTemplateStrings()['city'],
                'placeholder' => BackendStrings::getTemplateStrings()['city'],
                'required' => true,
                'parentId' => 6,
                'fieldIndex' => 6,
                'defaultValue' => '',
                'position' => 6,
                'hideLabel' => false,
                'description' => '',
                'addressType' => 'city',
                'settings' => json_encode(
                    [
                    'type' => 'city',
                    'hideLabel' => false,
                    'description' => '',
                    'placeholder' => BackendStrings::getTemplateStrings()['city'],
                    'requiredMessage' => '',
                    'visible' => true
                    ]
                ),
            ],
            // State/Province - Child of Address Field
            [
                'id' => 21,
                'type' => 'text',
                'label' => BackendStrings::getTemplateStrings()['state_province'],
                'placeholder' => BackendStrings::getTemplateStrings()['state'],
                'required' => true,
                'parentId' => 6,
                'fieldIndex' => 6,
                'defaultValue' => '',
                'position' => 6,
                'hideLabel' => false,
                'description' => '',
                'addressType' => 'state',
                'settings' => json_encode(
                    [
                    'type' => 'state',
                    'hideLabel' => false,
                    'description' => '',
                    'placeholder' => BackendStrings::getTemplateStrings()['state'],
                    'requiredMessage' => '',
                    'visible' => true
                    ]
                ),
            ],
            // Zip/Postal Code - Child of Address Field
            [
                'id' => 23,
                'type' => 'text',
                'label' => BackendStrings::getTemplateStrings()['zip_postal_code'],
                'placeholder' => BackendStrings::getTemplateStrings()['zip'],
                'required' => true,
                'parentId' => 6,
                'fieldIndex' => 6,
                'defaultValue' => '',
                'position' => 6,
                'hideLabel' => false,
                'description' => '',
                'addressType' => 'zip',
                'settings' => json_encode(
                    [
                    'type' => 'zip',
                    'hideLabel' => false,
                    'description' => '',
                    'placeholder' => BackendStrings::getTemplateStrings()['zip'],
                    'requiredMessage' => '',
                    'visible' => true
                    ]
                ),
            ],
            // Country - Child of Address Field
            [
                'id' => 25,
                'type' => 'text',
                'label' => BackendStrings::getTemplateStrings()['country'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_country'],
                'required' => true,
                'parentId' => 6,
                'fieldIndex' => 6,
                'defaultValue' => '',
                'position' => 6,
                'hideLabel' => false,
                'description' => '',
                'addressType' => 'country',
                'settings' => json_encode(
                    [
                    'type' => 'country',
                    'hideLabel' => false,
                    'description' => '',
                    'placeholder' => BackendStrings::getTemplateStrings()['select_country'],
                    'requiredMessage' => '',
                    'visible' => true
                    ]
                ),
            ],
        ];
    }

    /**
     * Get shipping method fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getShippingMethodFields(): array
    {
        return [
            [
                'id' => 29,
                'fieldIndex' => 7,
                'type' => 'select',
                'label' => BackendStrings::getTemplateStrings()['preferred_shipping_method'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_shipping_method'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 7,
                'fieldOptions' => [
                    [
                        'id' => 26,
                        'label' => BackendStrings::getTemplateStrings()['standard_shipping'],
                        'value' => 'standard',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 27,
                        'label' => BackendStrings::getTemplateStrings()['express_shipping'],
                        'value' => 'express',
                        'isDefault' => false,
                        'position' => 2
                    ],
                    [
                        'id' => 28,
                        'label' => BackendStrings::getTemplateStrings()['overnight_shipping'],
                        'value' => 'overnight',
                        'isDefault' => false,
                        'position' => 3
                    ],
                ]
            ],
        ];
    }

    /**
     * Get review paragraph field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getReviewParagraphField(): array
    {
        return [
            [
                'id' => 30,
                'fieldIndex' => 8,
                'type' => 'textarea',
                'label' => BackendStrings::getTemplateStrings()['order_review_message'],
                'parentId' => null,
                'position' => 8,
                'required' => false,
                'rows' => 3,
            ],
        ];
    }
}
