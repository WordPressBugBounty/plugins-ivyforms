<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class TechConferenceRegistrationFormTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'tech_conference_registration_form',
            'name' => BackendStrings::getTemplateStrings()['tech_conference_registration_form'],
            'description' => BackendStrings::getTemplateStrings()['tech_conference_registration_form_desc'],
            'category' => 'event-registration-forms',
            'subcategory' => 'conference-registration-forms',
            'is_pro' => false,
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'tech-conference-registration-form.svg'
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
                'name' => BackendStrings::getTemplateStrings()['tech_conference_registration_form'],
                'published' => 1,
                'showTitle' => 1,
                'storeEntries' => 1,
                'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
                'fields' => array_merge(
                    self::getParticipantInformationFields(),
                    self::getTechPreferencesFields(),
                    self::getAdditionalInformationFields()
                ),
                'settings' => []
            ]
            ]
        );
    }

    /**
     * Get participant information fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getParticipantInformationFields(): array
    {
        return [
            // Full Name
            [
                'id' => 1,
                'fieldIndex' => 1,
                'type' => 'text',
                'label' => BackendStrings::getTemplateStrings()['full_name'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_your_full_name'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 1,
                'validation' => [
                    'required' => true,
                ],
            ],
            // Email Address
            [
                'id' => 2,
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
                'id' => 3,
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
     * Get tech preferences fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getTechPreferencesFields(): array
    {
        return [
            // Conference Track Dropdown
            [
                'id' => 8,
                'fieldIndex' => 4,
                'type' => 'select',
                'label' => BackendStrings::getTemplateStrings()['conference_track'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_conference_track'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 4,
                'fieldOptions' => [
                    [
                        'id' => 4,
                        'label' => BackendStrings::getTemplateStrings()['ai_machine_learning'],
                        'value' => 'ai_machine_learning',
                        'isDefault' => false,
                        'position' => 1,
                    ],
                    [
                        'id' => 5,
                        'label' => BackendStrings::getTemplateStrings()['software_development'],
                        'value' => 'software_development',
                        'isDefault' => false,
                        'position' => 2,
                    ],
                    [
                        'id' => 6,
                        'label' => BackendStrings::getTemplateStrings()['cybersecurity'],
                        'value' => 'cybersecurity',
                        'isDefault' => false,
                        'position' => 3,
                    ],
                    [
                        'id' => 7,
                        'label' => BackendStrings::getTemplateStrings()['cloud_computing'],
                        'value' => 'cloud_computing',
                        'isDefault' => false,
                        'position' => 4,
                    ],
                ],
            ],
            // Sessions to Attend (Checkbox)
            [
                'id' => 13,
                'fieldIndex' => 5,
                'type' => 'checkbox',
                'label' => BackendStrings::getTemplateStrings()['sessions_to_attend'],
                'placeholder' => '',
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 5,
                'fieldOptions' => [
                    [
                        'id' => 9,
                        'label' => BackendStrings::getTemplateStrings()['session_1'],
                        'value' => 'session_1',
                        'isDefault' => false,
                        'position' => 1,
                    ],
                    [
                        'id' => 10,
                        'label' => BackendStrings::getTemplateStrings()['session_2'],
                        'value' => 'session_2',
                        'isDefault' => false,
                        'position' => 2,
                    ],
                    [
                        'id' => 11,
                        'label' => BackendStrings::getTemplateStrings()['session_3'],
                        'value' => 'session_3',
                        'isDefault' => false,
                        'position' => 3,
                    ],
                    [
                        'id' => 12,
                        'label' => BackendStrings::getTemplateStrings()['session_4'],
                        'value' => 'session_4',
                        'isDefault' => false,
                        'position' => 4,
                    ],
                ],
            ],
            // Participation Type (Radio)
            [
                'id' => 16,
                'fieldIndex' => 6,
                'type' => 'radio',
                'label' => BackendStrings::getTemplateStrings()['participation_type'],
                'placeholder' => '',
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 6,
                'fieldOptions' => [
                    [
                        'id' => 14,
                        'label' => BackendStrings::getTemplateStrings()['in_person'],
                        'value' => 'in_person',
                        'isDefault' => false,
                        'position' => 1,
                    ],
                    [
                        'id' => 15,
                        'label' => BackendStrings::getTemplateStrings()['virtual'],
                        'value' => 'virtual',
                        'isDefault' => false,
                        'position' => 2,
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
            // Additional Notes
            [
                'id' => 17,
                'fieldIndex' => 7,
                'type' => 'textarea',
                'label' => BackendStrings::getTemplateStrings()['additional_notes'],
                'placeholder' => BackendStrings::getTemplateStrings()['write_additional_notes'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 7,
                'rows' => 4,
            ],
        ];
    }
}
