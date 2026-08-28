<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class EventRegistrationTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'event_registration',
            'name' => BackendStrings::getTemplateStrings()['event_registration'],
            'description' => BackendStrings::getTemplateStrings()['event_registration_desc'],
            'category' => 'event-registration-forms',
            'is_pro' => false,
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'event-registration.svg'
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
                'name' => BackendStrings::getTemplateStrings()['event_registration'],
                'published' => 1,
                'showTitle' => 1,
                'storeEntries' => 1,
                'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
                'fields' => array_merge(
                    self::getPersonalInformationFields(),
                    self::getEventDetailsFields(),
                    self::getSessionPreferencesFields(),
                    self::getAdditionalPreferencesFields()
                ),
                'settings' => []
            ]
            ]
        );
    }

    /**
     * Get personal information fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getPersonalInformationFields(): array
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
                'placeholder' => BackendStrings::getTemplateStrings()['enter_your_email_address'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 2,
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
            // Event Type
            [
                'id' => 10,
                'fieldIndex' => 3,
                'type' => 'select',
                'label' => BackendStrings::getTemplateStrings()['event_type'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_event_type'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 3,
                'fieldOptions' => [
                    [
                        'id' => 5,
                        'label' => BackendStrings::getTemplateStrings()['conference'],
                        'value' => 'conference',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 6,
                        'label' => BackendStrings::getTemplateStrings()['workshop'],
                        'value' => 'workshop',
                        'isDefault' => false,
                        'position' => 2
                    ],
                    [
                        'id' => 7,
                        'label' => BackendStrings::getTemplateStrings()['seminar'],
                        'value' => 'seminar',
                        'isDefault' => false,
                        'position' => 3
                    ],
                    [
                        'id' => 8,
                        'label' => BackendStrings::getTemplateStrings()['networking_event'],
                        'value' => 'networking_event',
                        'isDefault' => false,
                        'position' => 4
                    ],
                    [
                        'id' => 9,
                        'label' => BackendStrings::getTemplateStrings()['other'],
                        'value' => 'other',
                        'isDefault' => false,
                        'position' => 5
                    ],
                ]
            ],
            // Attendance Type
            [
                'id' => 14,
                'fieldIndex' => 4,
                'type' => 'select',
                'label' => BackendStrings::getTemplateStrings()['attendance_type'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_attendance_type'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 4,
                'fieldOptions' => [
                    [
                        'id' => 11,
                        'label' => BackendStrings::getTemplateStrings()['in_person'],
                        'value' => 'in_person',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 12,
                        'label' => BackendStrings::getTemplateStrings()['virtual'],
                        'value' => 'virtual',
                        'isDefault' => false,
                        'position' => 2
                    ],
                    [
                        'id' => 13,
                        'label' => BackendStrings::getTemplateStrings()['hybrid'],
                        'value' => 'hybrid',
                        'isDefault' => false,
                        'position' => 3
                    ],
                ]
            ],
            // Role / Participant Type
            [
                'id' => 21,
                'fieldIndex' => 5,
                'type' => 'select',
                'label' => BackendStrings::getTemplateStrings()['role_participant_type'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_role'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 5,
                'fieldOptions' => [
                    [
                        'id' => 15,
                        'label' => BackendStrings::getTemplateStrings()['volunteer'],
                        'value' => 'volunteer',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 16,
                        'label' => BackendStrings::getTemplateStrings()['delegate'],
                        'value' => 'delegate',
                        'isDefault' => false,
                        'position' => 2
                    ],
                    [
                        'id' => 17,
                        'label' => BackendStrings::getTemplateStrings()['partner'],
                        'value' => 'partner',
                        'isDefault' => false,
                        'position' => 3
                    ],
                    [
                        'id' => 18,
                        'label' => BackendStrings::getTemplateStrings()['speaker'],
                        'value' => 'speaker',
                        'isDefault' => false,
                        'position' => 4
                    ],
                    [
                        'id' => 19,
                        'label' => BackendStrings::getTemplateStrings()['attendee'],
                        'value' => 'attendee',
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
            // Organization / Company Name
            [
                'id' => 22,
                'fieldIndex' => 6,
                'type' => 'text',
                'label' => BackendStrings::getTemplateStrings()['organization_company_name'],
                'placeholder' => '',
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 6,
            ],
            // Job Title
            [
                'id' => 23,
                'fieldIndex' => 7,
                'type' => 'text',
                'label' => BackendStrings::getTemplateStrings()['job_title'],
                'placeholder' => '',
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 7,
            ],
        ];
    }

    /**
     * Get session preferences fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getSessionPreferencesFields(): array
    {
        return [
            // Session Selection
            [
                'id' => 26,
                'fieldIndex' => 8,
                'type' => 'select',
                'label' => BackendStrings::getTemplateStrings()['session_selection'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_session'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 8,
                'fieldOptions' => [
                    [
                        'id' => 24,
                        'label' => BackendStrings::getTemplateStrings()['morning_session'],
                        'value' => 'morning_session',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 25,
                        'label' => BackendStrings::getTemplateStrings()['afternoon_session'],
                        'value' => 'afternoon_session',
                        'isDefault' => false,
                        'position' => 2
                    ],
                ]
            ],
            // Event Activity Selection
            [
                'id' => 29,
                'fieldIndex' => 9,
                'type' => 'select',
                'label' => BackendStrings::getTemplateStrings()['event_activity_selection'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_event_activity'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 9,
                'fieldOptions' => [
                    [
                        'id' => 27,
                        'label' => BackendStrings::getTemplateStrings()['networking_mixer'],
                        'value' => 'networking_mixer',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 28,
                        'label' => BackendStrings::getTemplateStrings()['keynote_only'],
                        'value' => 'keynote_only',
                        'isDefault' => false,
                        'position' => 2
                    ],
                ]
            ],
        ];
    }

    /**
     * Get additional preferences fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getAdditionalPreferencesFields(): array
    {
        return [
            // Meal Preference
            [
                'id' => 34,
                'fieldIndex' => 10,
                'type' => 'select',
                'label' => BackendStrings::getTemplateStrings()['meal_preference'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_meal_preference'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 10,
                'fieldOptions' => [
                    [
                        'id' => 30,
                        'label' => BackendStrings::getTemplateStrings()['vegetarian'],
                        'value' => 'vegetarian',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 31,
                        'label' => BackendStrings::getTemplateStrings()['vegan'],
                        'value' => 'vegan',
                        'isDefault' => false,
                        'position' => 2
                    ],
                    [
                        'id' => 32,
                        'label' => BackendStrings::getTemplateStrings()['gluten_free'],
                        'value' => 'gluten_free',
                        'isDefault' => false,
                        'position' => 3
                    ],
                    [
                        'id' => 33,
                        'label' => BackendStrings::getTemplateStrings()['no_preference'],
                        'value' => 'no_preference',
                        'isDefault' => false,
                        'position' => 4
                    ],
                ]
            ],
            // T-Shirt Size
            [
                'id' => 41,
                'fieldIndex' => 11,
                'type' => 'select',
                'label' => BackendStrings::getTemplateStrings()['t_shirt_size'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_t_shirt_size'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 11,
                'fieldOptions' => [
                    [
                        'id' => 35,
                        'label' => BackendStrings::getTemplateStrings()['xs'],
                        'value' => 'xs',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 36,
                        'label' => BackendStrings::getTemplateStrings()['s'],
                        'value' => 's',
                        'isDefault' => false,
                        'position' => 2
                    ],
                    [
                        'id' => 37,
                        'label' => BackendStrings::getTemplateStrings()['m'],
                        'value' => 'm',
                        'isDefault' => false,
                        'position' => 3
                    ],
                    [
                        'id' => 38,
                        'label' => BackendStrings::getTemplateStrings()['l'],
                        'value' => 'l',
                        'isDefault' => false,
                        'position' => 4
                    ],
                    [
                        'id' => 39,
                        'label' => BackendStrings::getTemplateStrings()['xl'],
                        'value' => 'xl',
                        'isDefault' => false,
                        'position' => 5
                    ],
                    [
                        'id' => 40,
                        'label' => BackendStrings::getTemplateStrings()['xxl'],
                        'value' => 'xxl',
                        'isDefault' => false,
                        'position' => 6
                    ],
                ]
            ],
            // Do you require special assistance?
            [
                'id' => 44,
                'fieldIndex' => 12,
                'type' => 'radio',
                'label' => BackendStrings::getTemplateStrings()['require_special_assistance'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'placeholder' => '',
                'position' => 12,
                'fieldOptions' => [
                    [
                        'id' => 42,
                        'label' => BackendStrings::getTemplateStrings()['yes'],
                        'value' => 'yes',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 43,
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
