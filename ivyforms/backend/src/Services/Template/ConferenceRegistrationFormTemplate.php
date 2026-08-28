<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class ConferenceRegistrationFormTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'conference_registration_form',
            'name' => BackendStrings::getTemplateStrings()['conference_registration_form'],
            'description' => BackendStrings::getTemplateStrings()['conference_registration_form_desc'],
            'category' => 'event-registration-forms',
            'is_pro' => false,
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'conference-registration-form.svg'
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
                'name' => BackendStrings::getTemplateStrings()['conference_registration_form'],
                'published' => 1,
                'showTitle' => 1,
                'storeEntries' => 1,
                'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
                'fields' => array_merge(
                    self::getPersonalInformationFields(),
                    self::getConferencePassFields(),
                    self::getSessionSelectionFields(),
                    self::getAccommodationFields(),
                    self::getNotificationsPreferencesFields()
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
                'label' => BackendStrings::getTemplateStrings()['name_first_last'],
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
                'placeholder' => BackendStrings::getTemplateStrings()['enter_your_full_name'],
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
                'placeholder' => '',
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
                'placeholder' => BackendStrings::getTemplateStrings()['email_address'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 2,
            ],
        ];
    }

    /**
     * Get conference pass fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getConferencePassFields(): array
    {
        return [
            // Access Pass
            [
                'id' => 8,
                'fieldIndex' => 3,
                'type' => 'select',
                'label' => BackendStrings::getTemplateStrings()['access_pass'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_access_pass'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 3,
                'fieldOptions' => [
                    [
                        'id' => 5,
                        'label' => BackendStrings::getTemplateStrings()['bronze_pass'],
                        'value' => 'bronze_199_95',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 6,
                        'label' => BackendStrings::getTemplateStrings()['silver_pass'],
                        'value' => 'silver_299_95',
                        'isDefault' => false,
                        'position' => 2
                    ],
                    [
                        'id' => 7,
                        'label' => BackendStrings::getTemplateStrings()['gold_pass'],
                        'value' => 'gold_399_95',
                        'isDefault' => false,
                        'position' => 3
                    ],
                ]
            ],
        ];
    }

    /**
     * Get session selection fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getSessionSelectionFields(): array
    {
        return [
            // Sessions to Attend
            [
                'id' => 14,
                'fieldIndex' => 4,
                'type' => 'checkbox',
                'label' => BackendStrings::getTemplateStrings()['sessions_to_attend'],
                'placeholder' => '',
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 4,
                'fieldOptions' => [
                    [
                        'id' => 9,
                        'label' => BackendStrings::getTemplateStrings()['session_1'],
                        'value' => 'session_1',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 10,
                        'label' => BackendStrings::getTemplateStrings()['session_2'],
                        'value' => 'session_2',
                        'isDefault' => false,
                        'position' => 2
                    ],
                    [
                        'id' => 11,
                        'label' => BackendStrings::getTemplateStrings()['session_3'],
                        'value' => 'session_3',
                        'isDefault' => false,
                        'position' => 3
                    ],
                    [
                        'id' => 12,
                        'label' => BackendStrings::getTemplateStrings()['session_4'],
                        'value' => 'session_4',
                        'isDefault' => false,
                        'position' => 4
                    ],
                    [
                        'id' => 13,
                        'label' => BackendStrings::getTemplateStrings()['session_5'],
                        'value' => 'session_5',
                        'isDefault' => false,
                        'position' => 5
                    ],
                ]
            ],
            // Attendance Type (Radio)
            [
                'id' => 17,
                'fieldIndex' => 5,
                'type' => 'radio',
                'label' => BackendStrings::getTemplateStrings()['attendance_type_radio'],
                'placeholder' => '',
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 5,
                'fieldOptions' => [
                    [
                        'id' => 15,
                        'label' => BackendStrings::getTemplateStrings()['in_person'],
                        'value' => 'in_person',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 16,
                        'label' => BackendStrings::getTemplateStrings()['virtual'],
                        'value' => 'virtual',
                        'isDefault' => false,
                        'position' => 2
                    ],
                ]
            ],
        ];
    }

    /**
     * Get accommodation fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getAccommodationFields(): array
    {
        return [
            // Will you be staying overnight?
            [
                'id' => 20,
                'fieldIndex' => 6,
                'type' => 'radio',
                'label' => BackendStrings::getTemplateStrings()['will_you_be_staying_overnight'],
                'placeholder' => '',
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 6,
                'fieldOptions' => [
                    [
                        'id' => 18,
                        'label' => BackendStrings::getTemplateStrings()['yes'],
                        'value' => 'yes',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 19,
                        'label' => BackendStrings::getTemplateStrings()['no'],
                        'value' => 'no',
                        'isDefault' => false,
                        'position' => 2
                    ],
                ]
            ],
        ];
    }

    /**
     * Get notifications and preferences fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getNotificationsPreferencesFields(): array
    {
        return [
            // Email Updates for Future Conferences
            [
                'id' => 23,
                'fieldIndex' => 7,
                'type' => 'radio',
                'label' => BackendStrings::getTemplateStrings()['email_updates_future_conferences'],
                'placeholder' => '',
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 7,
                'fieldOptions' => [
                    [
                        'id' => 21,
                        'label' => BackendStrings::getTemplateStrings()['yes'],
                        'value' => 'yes',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 22,
                        'label' => BackendStrings::getTemplateStrings()['no'],
                        'value' => 'no',
                        'isDefault' => false,
                        'position' => 2
                    ],
                ]
            ],
            // Comments or Questions
            [
                'id' => 24,
                'fieldIndex' => 8,
                'type' => 'textarea',
                'label' => BackendStrings::getTemplateStrings()['comments_or_questions'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_special_requests_questions_comments'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 8,
                'rows' => 4,
            ],
        ];
    }
}
