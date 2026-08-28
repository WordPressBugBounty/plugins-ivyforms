<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class TeamTshirtOrderFormTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'team_tshirt_order_form',
            'name' => BackendStrings::getTemplateStrings()['team_tshirt_order_form'],
            'description' => BackendStrings::getTemplateStrings()['team_tshirt_order_form_desc'],
            'category' => 'order-forms',
            'subcategory' => 'tshirt-order-forms',
            'is_pro' => false,
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'team-tshirt-order-form.svg'
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
                'name' => BackendStrings::getTemplateStrings()['team_tshirt_order_form'],
                'published' => 1,
                'showTitle' => 1,
                'storeEntries' => 1,
                'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
                'fields' => array_merge(
                    self::getContactInformationFields(),
                    self::getOrderDetailsFields(),
                    self::getAdditionalInformationFields()
                ),
                'settings' => []
            ]
            ]
        );
    }

    /**
     * Get contact information fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getContactInformationFields(): array
    {
        return [
            // Team Contact Name
            [
                'id' => 1,
                'fieldIndex' => 1,
                'type' => 'name',
                'label' => BackendStrings::getTemplateStrings()['team_contact_name'],
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
                'placeholder' => BackendStrings::getTemplateStrings()['enter_your_name'],
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
                'validation' => [
                    'required' => true,
                    'email' => true
                ],
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
            // Team Name
            [
                'id' => 6,
                'fieldIndex' => 4,
                'type' => 'text',
                'label' => BackendStrings::getTemplateStrings()['team_name'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_team_name'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 4,
            ],
        ];
    }

    /**
     * Get order details fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getOrderDetailsFields(): array
    {
        return [
            // T-Shirt Size(s) Dropdown
            [
                'id' => 13,
                'fieldIndex' => 5,
                'type' => 'select',
                'label' => BackendStrings::getTemplateStrings()['tshirt_sizes'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_tshirt_size'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 5,
                'fieldOptions' => [
                    [
                        'id' => 7,
                        'label' => BackendStrings::getTemplateStrings()['xs'],
                        'value' => 'xs',
                        'isDefault' => false,
                        'position' => 1,
                    ],
                    [
                        'id' => 8,
                        'label' => BackendStrings::getTemplateStrings()['s'],
                        'value' => 's',
                        'isDefault' => false,
                        'position' => 2,
                    ],
                    [
                        'id' => 9,
                        'label' => BackendStrings::getTemplateStrings()['m'],
                        'value' => 'm',
                        'isDefault' => false,
                        'position' => 3,
                    ],
                    [
                        'id' => 10,
                        'label' => BackendStrings::getTemplateStrings()['l'],
                        'value' => 'l',
                        'isDefault' => false,
                        'position' => 4,
                    ],
                    [
                        'id' => 11,
                        'label' => BackendStrings::getTemplateStrings()['xl'],
                        'value' => 'xl',
                        'isDefault' => false,
                        'position' => 5,
                    ],
                    [
                        'id' => 12,
                        'label' => BackendStrings::getTemplateStrings()['xxl'],
                        'value' => 'xxl',
                        'isDefault' => false,
                        'position' => 6,
                    ],
                ],
            ],
            // Quantity per Size
            [
                'id' => 14,
                'fieldIndex' => 6,
                'type' => 'number',
                'label' => BackendStrings::getTemplateStrings()['quantity_per_size'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_quantity'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 6,
            ],
            // Design Instructions
            [
                'id' => 15,
                'fieldIndex' => 7,
                'type' => 'textarea',
                'label' => BackendStrings::getTemplateStrings()['design_instructions'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_design_instructions'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 7,
                'rows' => 4,
            ],
            // T-Shirt Color Dropdown
            [
                'id' => 22,
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
                        'id' => 16,
                        'label' => BackendStrings::getTemplateStrings()['white'],
                        'value' => 'white',
                        'isDefault' => false,
                        'position' => 1,
                    ],
                    [
                        'id' => 17,
                        'label' => BackendStrings::getTemplateStrings()['black'],
                        'value' => 'black',
                        'isDefault' => false,
                        'position' => 2,
                    ],
                    [
                        'id' => 18,
                        'label' => BackendStrings::getTemplateStrings()['red'],
                        'value' => 'red',
                        'isDefault' => false,
                        'position' => 3,
                    ],
                    [
                        'id' => 19,
                        'label' => BackendStrings::getTemplateStrings()['blue'],
                        'value' => 'blue',
                        'isDefault' => false,
                        'position' => 4,
                    ],
                    [
                        'id' => 20,
                        'label' => BackendStrings::getTemplateStrings()['green'],
                        'value' => 'green',
                        'isDefault' => false,
                        'position' => 5,
                    ],
                    [
                        'id' => 21,
                        'label' => BackendStrings::getTemplateStrings()['custom'],
                        'value' => 'custom',
                        'isDefault' => false,
                        'position' => 6,
                    ],
                ],
            ],
        ];
    }

    /**
     * Get additional information fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getAdditionalInformationFields(): array
    {
        return [
            // Include Team Logo? Radio
            [
                'id' => 25,
                'fieldIndex' => 9,
                'type' => 'radio',
                'label' => BackendStrings::getTemplateStrings()['include_team_logo'],
                'placeholder' => '',
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 9,
                'fieldOptions' => [
                    [
                        'id' => 23,
                        'label' => BackendStrings::getTemplateStrings()['yes'],
                        'value' => 'yes',
                        'isDefault' => false,
                        'position' => 1,
                    ],
                    [
                        'id' => 24,
                        'label' => BackendStrings::getTemplateStrings()['no'],
                        'value' => 'no',
                        'isDefault' => false,
                        'position' => 2,
                    ],
                ],
            ],
            // Required Delivery Date
            [
                'id' => 26,
                'fieldIndex' => 10,
                'type' => 'date',
                'label' => BackendStrings::getTemplateStrings()['required_delivery_date'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_required_delivery_date'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 10,
            ],
        ];
    }
}
