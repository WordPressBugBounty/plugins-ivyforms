<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class TraditionalWeddingRSVPFormTemplate
{
/**
 * Traditional Wedding RSVP Form Template
 */
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'traditional_wedding_rsvp_form',
            'name' => BackendStrings::getTemplateStrings()['traditional_wedding_rsvp_form'],
            'description' => BackendStrings::getTemplateStrings()[
                'traditional_wedding_rsvp_form_description'
            ],
            'category' => 'event-registration-forms',
            'subcategory' => 'wedding-forms',
            'is_pro' => false,
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'traditional-wedding-rsvp-form.svg'
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
                'name' => BackendStrings::getTemplateStrings()['traditional_wedding_rsvp_form'],
                'published' => 1,
                'showTitle' => 1,
                'storeEntries' => 1,
                'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
                'fields' => array_merge(
                    self::getPersonalInformationFields(),
                    self::getAttendanceFields(),
                    self::getMealPreferencesFields(),
                    self::getTravelInformationFields(),
                    self::getAdditionalInformationFields()
                ),
                'settings' => []
            ]
            ]
        );
    }

    /**
     * Personal information fields (name, email, phone, relationship)
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getPersonalInformationFields(): array
    {
        return [
            // Full Name
            [
                'id' => 1,
                'fieldIndex' => 1,
                'type' => 'name',
                'label' => BackendStrings::getTemplateStrings()['full_name'],
                'parentId' => null,
                'defaultValue' => '',
                'placeholder' => BackendStrings::getTemplateStrings()['enter_your_full_name'],
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
            // Relationship to Couple
            [
                'id' => 10,
                'fieldIndex' => 4,
                'type' => 'select',
                'label' => BackendStrings::getTemplateStrings()['relationship_to_couple'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_relationship_to_couple'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 5,
                'fieldOptions' => [
                    [
                        'id' => 6,
                        'label' => BackendStrings::getTemplateStrings()['family'],
                        'value' => 'family',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 7,
                        'label' => BackendStrings::getTemplateStrings()['friend'],
                        'value' => 'friend',
                        'isDefault' => false,
                        'position' => 2
                    ],
                    [
                        'id' => 8,
                        'label' => BackendStrings::getTemplateStrings()['colleague'],
                        'value' => 'colleague',
                        'isDefault' => false,
                        'position' => 3
                    ],
                    [
                        'id' => 9,
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
     * Attendance fields (will you attend, number of guests)
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getAttendanceFields(): array
    {
        return [
            // Will you attend?
            [
                'id' => 13,
                'fieldIndex' => 5,
                'type' => 'radio',
                'label' => BackendStrings::getTemplateStrings()['will_you_attend'],
                'placeholder' => '',
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 6,
                'fieldOptions' => [
                    [
                        'id' => 11,
                        'label' => BackendStrings::getTemplateStrings()['yes'],
                        'value' => 'yes',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 12,
                        'label' => BackendStrings::getTemplateStrings()['no'],
                        'value' => 'no',
                        'isDefault' => false,
                        'position' => 2
                    ],
                ]
            ],
            // Number of Guests
            [
                'id' => 14,
                'fieldIndex' => 6,
                'type' => 'number',
                'label' => BackendStrings::getTemplateStrings()['number_of_guests'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_number_of_attendees'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 7,
                'minValue' => 1,
            ],
        ];
    }

    /**
     * Meal preferences fields (meal preference, dietary restrictions)
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getMealPreferencesFields(): array
    {
        return [
            // Meal Preference
            [
                'id' => 21,
                'fieldIndex' => 7,
                'type' => 'select',
                'label' => BackendStrings::getTemplateStrings()['meal_preference'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_meal_preference'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 8,
                'fieldOptions' => [
                    [
                        'id' => 15,
                        'label' => BackendStrings::getTemplateStrings()['beef'],
                        'value' => 'beef',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 16,
                        'label' => BackendStrings::getTemplateStrings()['chicken'],
                        'value' => 'chicken',
                        'isDefault' => false,
                        'position' => 2
                    ],
                    [
                        'id' => 17,
                        'label' => BackendStrings::getTemplateStrings()['fish'],
                        'value' => 'fish',
                        'isDefault' => false,
                        'position' => 3
                    ],
                    [
                        'id' => 18,
                        'label' => BackendStrings::getTemplateStrings()['vegetarian'],
                        'value' => 'vegetarian',
                        'isDefault' => false,
                        'position' => 4
                    ],
                    [
                        'id' => 19,
                        'label' => BackendStrings::getTemplateStrings()['vegan'],
                        'value' => 'vegan',
                        'isDefault' => false,
                        'position' => 5
                    ],
                    [
                        'id' => 20,
                        'label' => BackendStrings::getTemplateStrings()['other'],
                        'value' => 'other',
                        'isDefault' => false,
                        'position' => 6
                    ],
                ]
            ],
            // Dietary Restrictions or Notes
            [
                'id' => 22,
                'fieldIndex' => 8,
                'type' => 'textarea',
                'label' => BackendStrings::getTemplateStrings()['dietary_restrictions_or_notes'],
                'placeholder' => BackendStrings::getTemplateStrings()['specify_allergies_or_special_requests'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 9,
            ],
        ];
    }

    /**
     * Travel information fields (arrival date, departure date)
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getTravelInformationFields(): array
    {
        return [
            // Arrival Date
            [
                'id' => 23,
                'fieldIndex' => 9,
                'type' => 'date',
                'label' => BackendStrings::getTemplateStrings()['arrival_date'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_arrival_date'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 10,
                'dateFieldType' => 'picker',
                'dateFormat' => 'MM/DD/YYYY',
            ],
            // Departure Date
            [
                'id' => 24,
                'fieldIndex' => 10,
                'type' => 'date',
                'label' => BackendStrings::getTemplateStrings()['departure_date'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_departure_date'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 11,
                'dateFieldType' => 'picker',
                'dateFormat' => 'MM/DD/YYYY',
            ],
        ];
    }

    /**
     * Additional information fields (participate in activities, special requests)
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getAdditionalInformationFields(): array
    {
        return [
            // Participate in Additional Activities?
            [
                'id' => 27,
                'fieldIndex' => 11,
                'type' => 'radio',
                'label' => BackendStrings::getTemplateStrings()['participate_in_additional_activities'],
                'placeholder' => '',
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 12,
                'fieldOptions' => [
                    [
                        'id' => 25,
                        'label' => BackendStrings::getTemplateStrings()['yes'],
                        'value' => 'yes',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 26,
                        'label' => BackendStrings::getTemplateStrings()['no'],
                        'value' => 'no',
                        'isDefault' => false,
                        'position' => 2
                    ],
                ]
            ],
            // Special Requests or Messages
            [
                'id' => 28,
                'fieldIndex' => 12,
                'type' => 'textarea',
                'label' => BackendStrings::getTemplateStrings()['special_requests_or_messages'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_any_additional_notes'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 13,
            ],
        ];
    }
}
