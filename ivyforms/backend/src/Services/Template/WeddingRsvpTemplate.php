<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class WeddingRsvpTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'wedding_rsvp',
            'name' => BackendStrings::getTemplateStrings()['wedding_rsvp'],
            'description' => BackendStrings::getTemplateStrings()['wedding_rsvp_desc'],
            'category' => 'event-registration-forms',
            'subcategory' => 'wedding-forms',
            'is_pro' => false,
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'wedding-rsvp.svg'
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
                'name' => BackendStrings::getTemplateStrings()['wedding_rsvp'],
                'published' => 1,
                'showTitle' => 1,
                'storeEntries' => 1,
                'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
                'fields' => array_merge(
                    self::getGuestInformationFields(),
                    self::getAttendanceFields(),
                    self::getMealPreferenceFields(),
                    self::getSpecialRequestFields()
                ),
                'settings' => []
            ]
            ]
        );
    }

    /**
     * Get guest information fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getGuestInformationFields(): array
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
            // Email
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
            // Phone
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
     * Get attendance fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getAttendanceFields(): array
    {
        return [
            [
                'id' => 8,
                'fieldIndex' => 4,
                'type' => 'radio',
                'label' => BackendStrings::getTemplateStrings()['will_you_attend'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'placeholder' => '',
                'position' => 4,
                'fieldOptions' => [
                    [
                        'id' => 6,
                        'label' => BackendStrings::getTemplateStrings()['yes_i_will_attend'],
                        'value' => 'yes',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 7,
                        'label' => BackendStrings::getTemplateStrings()['no_i_cannot_attend'],
                        'value' => 'no',
                        'isDefault' => false,
                        'position' => 2
                    ],
                ]
            ],
            [
                'id' => 9,
                'fieldIndex' => 5,
                'type' => 'number',
                'label' => BackendStrings::getTemplateStrings()['number_of_guests'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_total_attending_with_you'],
                'required' => true,
                'parentId' => null,
                'position' => 5,
                'minValue' => 1,
                'maxValue' => 100,
            ],
        ];
    }

    /**
     * Get meal preference fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getMealPreferenceFields(): array
    {
        return [
            [
                'id' => 14,
                'fieldIndex' => 6,
                'type' => 'select',
                'label' => BackendStrings::getTemplateStrings()['meal_option'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_your_meal_preference'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 6,
                'fieldOptions' => [
                    [
                        'id' => 10,
                        'label' => BackendStrings::getTemplateStrings()['chicken'],
                        'value' => 'chicken',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 11,
                        'label' => BackendStrings::getTemplateStrings()['beef'],
                        'value' => 'beef',
                        'isDefault' => false,
                        'position' => 2
                    ],
                    [
                        'id' => 12,
                        'label' => BackendStrings::getTemplateStrings()['vegetarian'],
                        'value' => 'vegetarian',
                        'isDefault' => false,
                        'position' => 3
                    ],
                    [
                        'id' => 13,
                        'label' => BackendStrings::getTemplateStrings()['vegan'],
                        'value' => 'vegan',
                        'isDefault' => false,
                        'position' => 4
                    ],
                ]
            ],
            [
                'id' => 15,
                'fieldIndex' => 7,
                'type' => 'textarea',
                'label' => BackendStrings::getTemplateStrings()['dietary_restrictions_allergies'],
                'placeholder' => BackendStrings::getTemplateStrings()['dietary_restrictions_placeholder'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 7,
                'rows' => 3,
            ],
        ];
    }

    /**
     * Get special request fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getSpecialRequestFields(): array
    {
        return [
            [
                'id' => 16,
                'fieldIndex' => 8,
                'type' => 'textarea',
                'label' => BackendStrings::getTemplateStrings()['song_request_message_for_couple'],
                'placeholder' => BackendStrings::getTemplateStrings()['song_request_placeholder'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 8,
                'rows' => 3,
            ],
            [
                'id' => 19,
                'fieldIndex' => 9,
                'type' => 'radio',
                'label' => BackendStrings::getTemplateStrings()['require_special_assistance'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'placeholder' => '',
                'position' => 9,
                'fieldOptions' => [
                    [
                        'id' => 17,
                        'label' => BackendStrings::getTemplateStrings()['yes'],
                        'value' => 'yes',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 18,
                        'label' => BackendStrings::getTemplateStrings()['no'],
                        'value' => 'no',
                        'isDefault' => false,
                        'position' => 2
                    ],
                ]
            ],
        ];
    }
}
