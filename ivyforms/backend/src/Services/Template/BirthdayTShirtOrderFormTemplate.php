<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class BirthdayTShirtOrderFormTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'birthday_tshirt_order_form',
            'name' => BackendStrings::getTemplateStrings()['birthday_tshirt_order_form'],
            'description' => BackendStrings::getTemplateStrings()['birthday_tshirt_order_form_desc'],
            'category' => 'order-forms',
            'subcategory' => 'tshirt-order-forms',
            'is_pro' => false,
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'birthday-tshirt-order-form.svg'
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
                'name' => BackendStrings::getTemplateStrings()['birthday_tshirt_order_form'],
                'published' => 1,
                'showTitle' => 1,
                'storeEntries' => 1,
                'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
                'fields' => array_merge(
                    self::getOrdererInformationFields(),
                    self::getBirthdayDetailsFields(),
                    self::getTShirtDetailsFields(),
                    self::getDeliveryDetailsFields()
                ),
                'settings' => []
            ]
            ]
        );
    }

    /**
     * Get orderer information fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getOrdererInformationFields(): array
    {
        return [
            // Name Field - Parent
            [
                'id' => 1,
                'fieldIndex' => 1,
                'type' => 'name',
                'label' => BackendStrings::getTemplateStrings()['orderer_name'],
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
                'placeholder' => 'name@example.com',
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
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 3,
                'phoneAutoDetect' => true,
            ],
        ];
    }

    /**
     * Get birthday details fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getBirthdayDetailsFields(): array
    {
        return [
            // Birthday Person's Name
            [
                'id' => 6,
                'fieldIndex' => 4,
                'type' => 'text',
                'label' => BackendStrings::getTemplateStrings()['birthday_person_name'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_birthday_person_name'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 4,
            ],
        ];
    }

    /**
     * Get t-shirt details fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getTShirtDetailsFields(): array
    {
        return [
            // T-Shirt Size
            [
                'id' => 7,
                'fieldIndex' => 5,
                'type' => 'select',
                'label' => BackendStrings::getTemplateStrings()['tshirt_size'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_tshirt_size'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 5,
                'fieldOptions' => [
                    [
                        'id' => 8,
                        'label' => BackendStrings::getTemplateStrings()['xs'],
                        'value' => 'XS',
                        'position' => 1,
                        'isDefault' => false,
                    ],
                    [
                        'id' => 9,
                        'label' => BackendStrings::getTemplateStrings()['s'],
                        'value' => 'S',
                        'position' => 2,
                        'isDefault' => false,
                    ],
                    [
                        'id' => 10,
                        'label' => BackendStrings::getTemplateStrings()['m'],
                        'value' => 'M',
                        'position' => 3,
                        'isDefault' => false,
                    ],
                    [
                        'id' => 11,
                        'label' => BackendStrings::getTemplateStrings()['l'],
                        'value' => 'L',
                        'position' => 4,
                        'isDefault' => false,
                    ],
                    [
                        'id' => 12,
                        'label' => BackendStrings::getTemplateStrings()['xl'],
                        'value' => 'XL',
                        'position' => 5,
                        'isDefault' => false,
                    ],
                    [
                        'id' => 13,
                        'label' => BackendStrings::getTemplateStrings()['xxl'],
                        'value' => 'XXL',
                        'position' => 6,
                        'isDefault' => false,
                    ],
                ]
            ],
            // Quantity
            [
                'id' => 14,
                'fieldIndex' => 6,
                'type' => 'number',
                'label' => BackendStrings::getTemplateStrings()['quantity'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_quantity'],
                'required' => true,
                'parentId' => null,
                'position' => 6,
                'minValue' => 1,
                'maxValue' => 100,
            ],
            // Message or Design Notes
            [
                'id' => 15,
                'fieldIndex' => 7,
                'type' => 'textarea',
                'label' => BackendStrings::getTemplateStrings()['message_or_design_notes'],
                'placeholder' => BackendStrings::getTemplateStrings()['add_birthday_message_or_design_details'],
                'parentId' => null,
                'defaultValue' => '',
                'position' => 7,
                'required' => false,
                'rows' => 3,
            ],
            // T-Shirt Color
            [
                'id' => 16,
                'fieldIndex' => 8,
                'type' => 'select',
                'label' => BackendStrings::getTemplateStrings()['tshirt_color'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_tshirt_color'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 8,
                'fieldOptions' => [
                    [
                        'id' => 17,
                        'label' => BackendStrings::getTemplateStrings()['white'],
                        'value' => 'White',
                        'position' => 1,
                        'isDefault' => false,
                    ],
                    [
                        'id' => 18,
                        'label' => BackendStrings::getTemplateStrings()['black'],
                        'value' => 'Black',
                        'position' => 2,
                        'isDefault' => false,
                    ],
                    [
                        'id' => 19,
                        'label' => BackendStrings::getTemplateStrings()['red'],
                        'value' => 'Red',
                        'position' => 3,
                        'isDefault' => false,
                    ],
                    [
                        'id' => 20,
                        'label' => BackendStrings::getTemplateStrings()['blue'],
                        'value' => 'Blue',
                        'position' => 4,
                        'isDefault' => false,
                    ],
                    [
                        'id' => 21,
                        'label' => BackendStrings::getTemplateStrings()['green'],
                        'value' => 'Green',
                        'position' => 5,
                        'isDefault' => false,
                    ],
                    [
                        'id' => 22,
                        'label' => BackendStrings::getTemplateStrings()['custom'],
                        'value' => 'Custom',
                        'position' => 6,
                        'isDefault' => false,
                    ],
                ]
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
            // Include Gift Packaging
            [
                'id' => 23,
                'fieldIndex' => 9,
                'type' => 'radio',
                'label' => BackendStrings::getTemplateStrings()['include_gift_packaging'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'placeholder' => '',
                'position' => 9,
                'fieldOptions' => [
                    [
                        'id' => 24,
                        'label' => BackendStrings::getTemplateStrings()['yes'],
                        'value' => 'Yes',
                        'position' => 1,
                        'isDefault' => false,
                    ],
                    [
                        'id' => 25,
                        'label' => BackendStrings::getTemplateStrings()['no'],
                        'value' => 'No',
                        'position' => 2,
                        'isDefault' => false,
                    ],
                ]
            ],
            // Required Delivery Date
            [
                'id' => 26,
                'fieldIndex' => 10,
                'type' => 'date',
                'label' => BackendStrings::getTemplateStrings()['required_delivery_date'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_delivery_date'],
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
