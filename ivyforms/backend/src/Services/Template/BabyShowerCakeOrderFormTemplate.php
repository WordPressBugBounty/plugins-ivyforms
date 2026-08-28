<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class BabyShowerCakeOrderFormTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'baby_shower_cake_order_form',
            'name' => BackendStrings::getTemplateStrings()['baby_shower_cake_order_form'],
            'description' => BackendStrings::getTemplateStrings()['baby_shower_cake_order_form_desc'],
            'category' => 'order-forms',
            'subcategory' => 'cake-order-forms',
            'is_pro' => false,
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'baby-shower-cake-order-form.svg'
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
                'name' => BackendStrings::getTemplateStrings()['baby_shower_cake_order_form'],
                'published' => 1,
                'showTitle' => 1,
                'storeEntries' => 1,
                'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
                'fields' => array_merge(
                    self::getCustomerInformationFields(),
                    self::getCakeDetailsFields(),
                    self::getThemeAndDesignFields(),
                    self::getEventAndDeliveryFields()
                ),
                'settings' => []
            ]
            ]
        );
    }

    /**
     * Parent(s) Name(s), email, phone
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getCustomerInformationFields(): array
    {
        return [
            // Parent(s) Name(s)
            [
                'id' => 1,
                'fieldIndex' => 1,
                'type' => 'text',
                'label' => BackendStrings::getTemplateStrings()['parents_names'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_parents_names'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 1,
            ],
            // Email Address
            [
                'id' => 2,
                'fieldIndex' => 2,
                'type' => 'email',
                'label' => BackendStrings::getTemplateStrings()['email_address'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_your_email'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 2,
            ],
            // Phone Number
            [
                'id' => 3,
                'fieldIndex' => 3,
                'type' => 'phone',
                'label' => BackendStrings::getTemplateStrings()['phone_number'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_your_phone_number'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 3,
                'phoneAutoDetect' => true,
            ],
        ];
    }

    /**
     * Cake flavor and size
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getCakeDetailsFields(): array
    {
        return [
            // Cake Flavor
            [
                'id' => 9,
                'fieldIndex' => 4,
                'type' => 'select',
                'label' => BackendStrings::getTemplateStrings()['cake_flavor'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_cake_flavor'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 4,
                'fieldOptions' => [
                    [
                        'id' => 4,
                        'label' => BackendStrings::getTemplateStrings()['chocolate'],
                        'value' => 'chocolate',
                        'isDefault' => false,
                        'position' => 1,
                    ],
                    [
                        'id' => 5,
                        'label' => BackendStrings::getTemplateStrings()['vanilla'],
                        'value' => 'vanilla',
                        'isDefault' => false,
                        'position' => 2,
                    ],
                    [
                        'id' => 6,
                        'label' => BackendStrings::getTemplateStrings()['strawberry'],
                        'value' => 'strawberry',
                        'isDefault' => false,
                        'position' => 3,
                    ],
                    [
                        'id' => 7,
                        'label' => BackendStrings::getTemplateStrings()['lemon'],
                        'value' => 'lemon',
                        'isDefault' => false,
                        'position' => 4,
                    ],
                    [
                        'id' => 8,
                        'label' => BackendStrings::getTemplateStrings()['custom'],
                        'value' => 'custom',
                        'isDefault' => false,
                        'position' => 5,
                    ],
                ],
            ],
            // Cake Size
            [
                'id' => 13,
                'fieldIndex' => 5,
                'type' => 'select',
                'label' => BackendStrings::getTemplateStrings()['cake_size'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_cake_size'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 5,
                'fieldOptions' => [
                    [
                        'id' => 10,
                        'label' => BackendStrings::getTemplateStrings()['small_6_inch'],
                        'value' => 'small_6',
                        'isDefault' => false,
                        'position' => 1,
                    ],
                    [
                        'id' => 11,
                        'label' => BackendStrings::getTemplateStrings()['medium_8_inch'],
                        'value' => 'medium_8',
                        'isDefault' => false,
                        'position' => 2,
                    ],
                    [
                        'id' => 12,
                        'label' => BackendStrings::getTemplateStrings()['large_10_inch'],
                        'value' => 'large_10',
                        'isDefault' => false,
                        'position' => 3,
                    ],
                ],
            ],
        ];
    }

    /**
     * Theme, cake message, design ideas
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getThemeAndDesignFields(): array
    {
        return [
            // Baby Shower Theme
            [
                'id' => 14,
                'fieldIndex' => 6,
                'type' => 'text',
                'label' => BackendStrings::getTemplateStrings()['baby_shower_theme'],
                'placeholder' => BackendStrings::getTemplateStrings()['eg_little_prince_twinklle_pink_gold'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 6,
            ],
            // Cake Message
            [
                'id' => 15,
                'fieldIndex' => 7,
                'type' => 'text',
                'label' => BackendStrings::getTemplateStrings()['cake_message'],
                'placeholder' => BackendStrings::getTemplateStrings()['welcome_baby_emma'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 7,
            ],
            // Design Ideas / Requests
            [
                'id' => 16,
                'fieldIndex' => 8,
                'type' => 'textarea',
                'label' => BackendStrings::getTemplateStrings()['design_ideas_requests'],
                'placeholder' => BackendStrings::getTemplateStrings()['describe_your_design_ideas_or_color_scheme'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 8,
                'rows' => 4,
            ],
        ];
    }

    /**
     * Event date and delivery address (address field structure)
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getEventAndDeliveryFields(): array
    {
        return array_merge(
            self::getEventDateFields(),
            self::getDeliveryAddressFields()
        );
    }

    /**
     * Event date fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getEventDateFields(): array
    {
        return [
            // Event Date
            [
                'id' => 17,
                'fieldIndex' => 9,
                'type' => 'date',
                'label' => BackendStrings::getTemplateStrings()['event_date'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_event_date'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 9,
                'dateFieldType' => 'picker',
                'dateFormat' => 'MM/DD/YYYY',
            ],
        ];
    }

    /**
     * Delivery address fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getDeliveryAddressFields(): array
    {
        return [
            // Delivery Address Field - Parent
            [
                'id' => 18,
                'fieldIndex' => 10,
                'type' => 'address',
                'label' => BackendStrings::getTemplateStrings()['delivery_address'],
                'parentId' => null,
                'defaultValue' => '',
                'placeholder' => '',
                'readonly' => false,
                'position' => 10,
            ],
            // Address Line 1 - Child of Address Field (required)
            [
                'id' => 20,
                'type' => 'text',
                'label' => BackendStrings::getTemplateStrings()['address_line_1'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_address_line_1'],
                'required' => true,
                'parentId' => 10,
                'fieldIndex' => 10,
                'defaultValue' => '',
                'position' => 1,
                'hideLabel' => false,
                'description' => '',
                'addressType' => 'streetAddress',
                'settings' => json_encode(
                    [
                        'type' => 'streetAddress',
                        'hideLabel' => false,
                        'description' => '',
                        'placeholder' => BackendStrings::getTemplateStrings()['enter_address_line_1'],
                        'requiredMessage' => '',
                        'visible' => true,
                    ]
                ),
            ],
            // Address Line 2 - Child of Address Field (NOT required)
            [
                'id' => 22,
                'type' => 'text',
                'label' => BackendStrings::getTemplateStrings()['address_line_2'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_address_line_2'],
                'required' => false,
                'parentId' => 10,
                'fieldIndex' => 10,
                'defaultValue' => '',
                'position' => 2,
                'hideLabel' => false,
                'description' => '',
                'addressType' => 'addressLine2',
                'settings' => json_encode(
                    [
                        'type' => 'addressLine2',
                        'hideLabel' => false,
                        'description' => '',
                        'placeholder' => BackendStrings::getTemplateStrings()['enter_address_line_2'],
                        'requiredMessage' => '',
                        'visible' => true,
                    ]
                ),
            ],
            // City - Child of Address Field (required)
            [
                'id' => 24,
                'type' => 'text',
                'label' => BackendStrings::getTemplateStrings()['city'],
                'placeholder' => BackendStrings::getTemplateStrings()['city'],
                'required' => true,
                'parentId' => 10,
                'fieldIndex' => 10,
                'defaultValue' => '',
                'position' => 3,
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
                        'visible' => true,
                    ]
                ),
            ],
            // State/Province - Child of Address Field (required)
            [
                'id' => 26,
                'type' => 'text',
                'label' => BackendStrings::getTemplateStrings()['state_province'],
                'placeholder' => BackendStrings::getTemplateStrings()['state'],
                'required' => true,
                'parentId' => 10,
                'fieldIndex' => 10,
                'defaultValue' => '',
                'position' => 4,
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
                        'visible' => true,
                    ]
                ),
            ],
            // Zip/Postal Code - Child of Address Field (required)
            [
                'id' => 28,
                'type' => 'text',
                'label' => BackendStrings::getTemplateStrings()['zip_postal_code'],
                'placeholder' => BackendStrings::getTemplateStrings()['zip'],
                'required' => true,
                'parentId' => 10,
                'fieldIndex' => 10,
                'defaultValue' => '',
                'position' => 5,
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
                        'visible' => true,
                    ]
                ),
            ],
            // Country - Child of Address Field (required)
            [
                'id' => 30,
                'type' => 'text',
                'label' => BackendStrings::getTemplateStrings()['country'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_country'],
                'required' => true,
                'parentId' => 10,
                'fieldIndex' => 10,
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
                        'visible' => true,
                    ]
                ),
            ],
        ];
    }
}
