<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class EventTShirtOrderFormTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'event_tshirt_order_form',
            'name' => BackendStrings::getTemplateStrings()['event_tshirt_order_form'],
            'description' => BackendStrings::getTemplateStrings()['event_tshirt_order_form_desc'],
            'category' => 'order-forms',
            'subcategory' => 'tshirt-order-forms',
            'is_pro' => false,
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'event-tshirt-order-form.svg'
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
                'name' => BackendStrings::getTemplateStrings()['event_tshirt_order_form'],
                'published' => 1,
                'showTitle' => 1,
                'storeEntries' => 1,
                'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
                'fields' => array_merge(
                    self::getRequestorInformationFields(),
                    self::getEventDetailsFields(),
                    self::getTShirtDetailsFields(),
                    self::getAdditionalOptionsFields()
                ),
                'settings' => []
            ]
            ]
        );
    }

    /**
     * Get requestor information fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getRequestorInformationFields(): array
    {
        return [
            // Name Field - Parent
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
                'placeholder' => BackendStrings::getTemplateStrings()['enter_your_email_address'],
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
     * Get event details fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getEventDetailsFields(): array
    {
        return [
            // Event Name
            [
                'id' => 6,
                'fieldIndex' => 4,
                'type' => 'text',
                'label' => BackendStrings::getTemplateStrings()['event_name'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_event_name'],
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
                'id' => 13,
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
                        'id' => 7,
                        'label' => BackendStrings::getTemplateStrings()['xs'],
                        'value' => 'xs',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 8,
                        'label' => BackendStrings::getTemplateStrings()['s'],
                        'value' => 's',
                        'isDefault' => false,
                        'position' => 2
                    ],
                    [
                        'id' => 9,
                        'label' => BackendStrings::getTemplateStrings()['m'],
                        'value' => 'm',
                        'isDefault' => false,
                        'position' => 3
                    ],
                    [
                        'id' => 10,
                        'label' => BackendStrings::getTemplateStrings()['l'],
                        'value' => 'l',
                        'isDefault' => false,
                        'position' => 4
                    ],
                    [
                        'id' => 11,
                        'label' => BackendStrings::getTemplateStrings()['xl'],
                        'value' => 'xl',
                        'isDefault' => false,
                        'position' => 5
                    ],
                    [
                        'id' => 12,
                        'label' => BackendStrings::getTemplateStrings()['xxl'],
                        'value' => 'xxl',
                        'isDefault' => false,
                        'position' => 6
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
            // Event Notes or Special Instructions
            [
                'id' => 15,
                'fieldIndex' => 7,
                'type' => 'textarea',
                'label' => BackendStrings::getTemplateStrings()['event_notes_special_instructions'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_event_notes'],
                'parentId' => null,
                'defaultValue' => '',
                'position' => 7,
                'required' => false,
                'rows' => 3,
            ],
            // T-Shirt Color
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
                        'position' => 1
                    ],
                    [
                        'id' => 17,
                        'label' => BackendStrings::getTemplateStrings()['black'],
                        'value' => 'black',
                        'isDefault' => false,
                        'position' => 2
                    ],
                    [
                        'id' => 18,
                        'label' => BackendStrings::getTemplateStrings()['red'],
                        'value' => 'red',
                        'isDefault' => false,
                        'position' => 3
                    ],
                    [
                        'id' => 19,
                        'label' => BackendStrings::getTemplateStrings()['blue'],
                        'value' => 'blue',
                        'isDefault' => false,
                        'position' => 4
                    ],
                    [
                        'id' => 20,
                        'label' => BackendStrings::getTemplateStrings()['green'],
                        'value' => 'green',
                        'isDefault' => false,
                        'position' => 5
                    ],
                    [
                        'id' => 21,
                        'label' => BackendStrings::getTemplateStrings()['custom'],
                        'value' => 'custom',
                        'isDefault' => false,
                        'position' => 6
                    ],
                ]
            ],
        ];
    }

    /**
     * Get additional options fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getAdditionalOptionsFields(): array
    {
        return [
            // Include Event Logo
            [
                'id' => 25,
                'fieldIndex' => 9,
                'type' => 'radio',
                'label' => BackendStrings::getTemplateStrings()['include_event_logo'],
                'parentId' => null,
                'position' => 9,
                'required' => false,
                'fieldOptions' => [
                    [
                        'id' => 23,
                        'label' => BackendStrings::getTemplateStrings()['yes'],
                        'value' => 'yes',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 24,
                        'label' => BackendStrings::getTemplateStrings()['no'],
                        'value' => 'no',
                        'isDefault' => false,
                        'position' => 2
                    ],
                ]
            ],
            // Event Date - moved to last position
            [
                'id' => 26,
                'fieldIndex' => 10,
                'type' => 'date',
                'label' => BackendStrings::getTemplateStrings()['event_date'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_event_date'],
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
