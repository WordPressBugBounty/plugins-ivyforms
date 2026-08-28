<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class CustomCakeOrderFormTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'custom_cake_order_form',
            'name' => BackendStrings::getTemplateStrings()['custom_cake_order_form'],
            'description' => BackendStrings::getTemplateStrings()['custom_cake_order_form_desc'],
            'category' => 'order-forms',
            'subcategory' => 'cake-order-forms',
            'is_pro' => false,
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'custom-cake-order-form.svg'
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
                'name' => BackendStrings::getTemplateStrings()['custom_cake_order_form'],
                'published' => 1,
                'showTitle' => 1,
                'storeEntries' => 1,
                'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
                'fields' => array_merge(
                    self::getCustomerInformationFields(),
                    self::getCakeDetailsFields(),
                    self::getDeliveryDetailsFields()
                ),
                'settings' => []
            ]
            ]
        );
    }

    /**
     * Get customer information fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getCustomerInformationFields(): array
    {
        return [
            // Name Field - Parent
            [
                'id' => 1,
                'fieldIndex' => 1,
                'type' => 'name',
                'label' => BackendStrings::getTemplateStrings()['full_name'],
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
                'label' => BackendStrings::getNewFormStrings()['first_name'],
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
                'label' => BackendStrings::getNewFormStrings()['last_name'],
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
                'placeholder' => 'enter_your_email_address',
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 2,
            ],
            // Phone Number
            [
                'id' => 5,
                'fieldIndex' => 3,
                'type' => 'phone',
                'label' => BackendStrings::getTemplateStrings()['phone_number'],
                'placeholder' => '+1 (_) _-_',
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 3,
                'phoneAutoDetect' => true,
            ],
        ];
    }

    /**
     * Get cake details fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getCakeDetailsFields(): array
    {
        return array_merge(
            self::getOccasionField(),
            self::getCakeFlavorField(),
            self::getCakeSizeField(),
            self::getDesignInspirationField(),
            self::getCakeMessageField()
        );
    }

    /**
     * Get occasion field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getOccasionField(): array
    {
        return [
            [
                'id' => 12,
                'fieldIndex' => 4,
                'type' => 'select',
                'label' => BackendStrings::getTemplateStrings()['occasion'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_the_occasion'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 4,
                'fieldOptions' => [
                    [
                        'id' => 6,
                        'label' => BackendStrings::getTemplateStrings()['birthday'],
                        'value' => 'birthday',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 7,
                        'label' => BackendStrings::getTemplateStrings()['wedding'],
                        'value' => 'wedding',
                        'isDefault' => false,
                        'position' => 2
                    ],
                    [
                        'id' => 8,
                        'label' => BackendStrings::getTemplateStrings()['anniversary'],
                        'value' => 'anniversary',
                        'isDefault' => false,
                        'position' => 3
                    ],
                    [
                        'id' => 9,
                        'label' => BackendStrings::getTemplateStrings()['baby_shower'],
                        'value' => 'baby_shower',
                        'isDefault' => false,
                        'position' => 4
                    ],
                    [
                        'id' => 10,
                        'label' => BackendStrings::getTemplateStrings()['graduation'],
                        'value' => 'graduation',
                        'isDefault' => false,
                        'position' => 5
                    ],
                    [
                        'id' => 11,
                        'label' => BackendStrings::getTemplateStrings()['other'],
                        'value' => 'other',
                        'isDefault' => false,
                        'position' => 6
                    ],
                ]
            ],
        ];
    }

    /**
     * Get cake flavor field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getCakeFlavorField(): array
    {
        return [
            [
                'id' => 19,
                'fieldIndex' => 5,
                'type' => 'select',
                'label' => BackendStrings::getTemplateStrings()['cake_flavor'],
                'placeholder' => BackendStrings::getTemplateStrings()['choose_cake_flavor'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 5,
                'fieldOptions' => [
                    [
                        'id' => 13,
                        'label' => BackendStrings::getTemplateStrings()['chocolate'],
                        'value' => 'chocolate',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 14,
                        'label' => BackendStrings::getTemplateStrings()['vanilla'],
                        'value' => 'vanilla',
                        'isDefault' => false,
                        'position' => 2
                    ],
                    [
                        'id' => 15,
                        'label' => BackendStrings::getTemplateStrings()['red_velvet'],
                        'value' => 'red_velvet',
                        'isDefault' => false,
                        'position' => 3
                    ],
                    [
                        'id' => 16,
                        'label' => BackendStrings::getTemplateStrings()['lemon'],
                        'value' => 'lemon',
                        'isDefault' => false,
                        'position' => 4
                    ],
                    [
                        'id' => 17,
                        'label' => BackendStrings::getTemplateStrings()['marble'],
                        'value' => 'marble',
                        'isDefault' => false,
                        'position' => 5
                    ],
                    [
                        'id' => 18,
                        'label' => BackendStrings::getTemplateStrings()['other'],
                        'value' => 'other',
                        'isDefault' => false,
                        'position' => 6
                    ],
                ]
            ],
        ];
    }

    /**
     * Get cake size field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getCakeSizeField(): array
    {
        return [
            [
                'id' => 24,
                'fieldIndex' => 6,
                'type' => 'select',
                'label' => BackendStrings::getTemplateStrings()['cake_size'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_cake_size'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 6,
                'fieldOptions' => [
                    [
                        'id' => 20,
                        'label' => BackendStrings::getTemplateStrings()['6_inch_serves_8'],
                        'value' => '6_inch',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 21,
                        'label' => BackendStrings::getTemplateStrings()['8_inch_serves_12'],
                        'value' => '8_inch',
                        'isDefault' => false,
                        'position' => 2
                    ],
                    [
                        'id' => 22,
                        'label' => BackendStrings::getTemplateStrings()['10_inch_serves_20'],
                        'value' => '10_inch',
                        'isDefault' => false,
                        'position' => 3
                    ],
                    [
                        'id' => 23,
                        'label' => BackendStrings::getTemplateStrings()['tiered_custom_size'],
                        'value' => 'tiered_custom',
                        'isDefault' => false,
                        'position' => 4
                    ],
                ]
            ],
        ];
    }

    /**
     * Get design inspiration field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getDesignInspirationField(): array
    {
        return [
            [
                'id' => 25,
                'fieldIndex' => 7,
                'type' => 'website',
                'label' => BackendStrings::getTemplateStrings()['design_inspiration_link'],
                'placeholder' => BackendStrings::getTemplateStrings()['paste_link_design_inspiration'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 7,
            ],
        ];
    }

    /**
     * Get cake message field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getCakeMessageField(): array
    {
        return [
            [
                'id' => 26,
                'fieldIndex' => 8,
                'type' => 'text',
                'label' => BackendStrings::getTemplateStrings()['cake_message'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_short_message_for_cake'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 8,
            ],
        ];
    }

    /**
     * Get delivery details fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getDeliveryDetailsFields(): array
    {
        return [
            // Delivery Date
            [
                'id' => 27,
                'fieldIndex' => 9,
                'type' => 'date',
                'label' => BackendStrings::getTemplateStrings()['delivery_date'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_delivery_date'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 9,
                'dateFieldType' => 'picker',
                'dateFormat' => 'MM/DD/YYYY',
            ],
            // Additional Notes
            [
                'id' => 28,
                'fieldIndex' => 10,
                'type' => 'textarea',
                'label' => BackendStrings::getTemplateStrings()['additional_notes'],
                'placeholder' => BackendStrings::getTemplateStrings()['write_additional_instructions_or_preferences'],
                'parentId' => null,
                'defaultValue' => '',
                'position' => 10,
                'required' => false,
                'rows' => 3,
            ],
        ];
    }
}
